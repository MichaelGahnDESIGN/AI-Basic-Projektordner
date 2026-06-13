import type { AgentCommsState } from "./types.ts";

export function cloneState(state: AgentCommsState): AgentCommsState {
  return structuredClone(state);
}
