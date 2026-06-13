import type { ToolCallResult } from "./toolTypes.ts";

export function jsonResult(value: unknown): ToolCallResult {
  return { content: [{ type: "text", text: JSON.stringify(value, null, 2) }] };
}
