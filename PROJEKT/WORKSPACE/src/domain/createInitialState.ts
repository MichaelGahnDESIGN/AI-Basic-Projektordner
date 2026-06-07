import type { AgentCommsState } from "./types.ts";

export function createInitialState(projectName = "Claude-Codex-MCP"): AgentCommsState {
  const now = new Date().toISOString();

  return {
    version: 1,
    projectName,
    updatedAt: now,
    counters: { task: 0, chat: 0, blocker: 0, decision: 0, handoff: 0, log: 0, history: 0 },
    tasks: [],
    done: [],
    chat: [],
    blockers: [],
    decisions: [],
    handoffs: [],
    logs: [],
    history: []
  };
}
