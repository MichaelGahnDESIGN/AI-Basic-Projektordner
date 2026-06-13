import { cloneState } from "./cloneState.ts";
import { nextId } from "./id.ts";
import type { AgentCommsState } from "./types.ts";

export interface ResetRoundInput {
  actor: string;
  summary: string;
}

export function resetRound(state: AgentCommsState, input: ResetRoundInput): AgentCommsState {
  const next = cloneState(state);
  const now = new Date().toISOString();
  next.history.push({ id: nextId(next.counters, "history"), createdAt: now, summary: input.summary, archivedTaskIds: next.tasks.map((task) => task.id), archivedDoneIds: next.done.map((task) => task.id) });
  next.tasks = [];
  next.done = [];
  next.chat = [];
  next.blockers = next.blockers.filter((blocker) => blocker.status === "OPEN");
  next.handoffs = [];
  next.updatedAt = now;
  next.logs.push({ id: nextId(next.counters, "log"), createdAt: now, actor: input.actor, event: "ROUND_RESET", message: "Runde wurde zurückgesetzt und in der History zusammengefasst." });
  return next;
}
