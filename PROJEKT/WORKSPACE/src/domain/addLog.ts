import { nextId } from "./id.ts";
import type { AgentCommsState } from "./types.ts";

export function addLog(state: AgentCommsState, actor: string, event: string, message: string): void {
  state.logs.push({ id: nextId(state.counters, "log"), createdAt: new Date().toISOString(), actor, event, message });
}
