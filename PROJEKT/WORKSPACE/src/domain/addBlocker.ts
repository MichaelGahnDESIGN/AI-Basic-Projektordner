import { addLog } from "./addLog.ts";
import { cloneState } from "./cloneState.ts";
import { nextId } from "./id.ts";
import type { AgentCommsState } from "./types.ts";

export interface AddBlockerInput {
  reporter: string;
  message: string;
  relatedTaskId?: string;
}

export function addBlocker(state: AgentCommsState, input: AddBlockerInput): AgentCommsState {
  const next = cloneState(state);
  const now = new Date().toISOString();
  const blockerId = nextId(next.counters, "blocker");
  next.blockers.push({ id: blockerId, createdAt: now, updatedAt: now, reporter: input.reporter, message: input.message, status: "OPEN", relatedTaskId: input.relatedTaskId });
  next.updatedAt = now;
  addLog(next, input.reporter, "BLOCKER_ADDED", `Blocker ${blockerId} dokumentiert.`);
  return next;
}
