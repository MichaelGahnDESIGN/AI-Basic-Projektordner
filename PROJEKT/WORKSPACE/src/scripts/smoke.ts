import assert from "node:assert/strict";
import { mkdtemp, rm } from "node:fs/promises";
import { tmpdir } from "node:os";
import { join } from "node:path";
import { createServer } from "../mcp/createServer.ts";

const tempDir = await mkdtemp(join(tmpdir(), "agent-comms-smoke-"));

try {
  const server = createServer({ baseDir: tempDir });
  const response = await server.handle({ jsonrpc: "2.0", id: 1, method: "tools/list", params: {} });
  assert.ok(response && "result" in response);
  const result = response.result as { tools: Array<{ name: string }> };
  assert.equal(result.tools.length, 12);
  assert.ok(result.tools.some((tool) => tool.name === "create_task"));
} finally {
  await rm(tempDir, { recursive: true, force: true });
}
