import readline from "node:readline";
import type { JsonRpcError, JsonRpcRequest, JsonRpcSuccess } from "./jsonRpcTypes.ts";
import type { RegisteredTool } from "../tools/toolTypes.ts";

export interface McpServerOptions {
  name: string;
  version: string;
  tools: RegisteredTool[];
}

export class McpServer {
  private readonly options: McpServerOptions;
  private readonly toolsByName: Map<string, RegisteredTool>;

  constructor(options: McpServerOptions) {
    this.options = options;
    this.toolsByName = new Map(options.tools.map((tool) => [tool.name, tool]));
  }

  async handle(request: JsonRpcRequest): Promise<JsonRpcSuccess | JsonRpcError | undefined> {
    try {
      const result = await this.dispatch(request);
      return request.id === undefined ? undefined : { jsonrpc: "2.0", id: request.id, result };
    } catch (error) {
      return request.id === undefined ? undefined : { jsonrpc: "2.0", id: request.id, error: { code: -32603, message: error instanceof Error ? error.message : "Unbekannter Fehler." } };
    }
  }

  async listen(): Promise<void> {
    const lines = readline.createInterface({ input: process.stdin, crlfDelay: Infinity });
    for await (const line of lines) {
      if (!line.trim()) {
        continue;
      }
      const response = await this.handle(JSON.parse(line) as JsonRpcRequest);
      if (response) {
        process.stdout.write(`${JSON.stringify(response)}\n`);
      }
    }
  }

  private async dispatch(request: JsonRpcRequest): Promise<unknown> {
    if (request.method === "initialize") {
      return { protocolVersion: "2024-11-05", capabilities: { tools: {} }, serverInfo: { name: this.options.name, version: this.options.version } };
    }
    if (request.method === "tools/list") {
      return { tools: this.options.tools.map(({ handler: _handler, ...tool }) => tool) };
    }
    if (request.method === "tools/call") {
      const params = request.params ?? {};
      const name = typeof params.name === "string" ? params.name : "";
      const tool = this.toolsByName.get(name);
      if (!tool) {
        throw new Error(`Tool ${name} ist nicht registriert.`);
      }
      return tool.handler(params.arguments ?? {});
    }
    if (request.method === "notifications/initialized") {
      return {};
    }
    throw new Error(`MCP-Methode ${request.method} wird nicht unterstützt.`);
  }
}
