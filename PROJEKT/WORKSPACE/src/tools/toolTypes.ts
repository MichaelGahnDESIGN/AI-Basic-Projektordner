export interface ToolDefinition {
  name: string;
  description: string;
  inputSchema: Record<string, unknown>;
}

export interface ToolCallResult {
  content: Array<{ type: "text"; text: string }>;
}

export type ToolHandler = (input: unknown) => Promise<ToolCallResult>;
export interface RegisteredTool extends ToolDefinition {
  handler: ToolHandler;
}
