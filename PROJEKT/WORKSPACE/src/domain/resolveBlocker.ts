import { addLog } from "./addLog.ts";
import { cloneState } from "./cloneState.ts";
import type { AgentCommsState } from "./types.ts";

export interface ResolveBlockerInput {
  blockerId: string;
  resolvedBy: string;
  resolutionNote: string;
}

export function resolveBlocker(state: AgentCommsState, input: ResolveBlockerInput): AgentCommsState {
  const next = cloneState(state);
  const blocker = next.blockers.find((candidate) => candidate.id === input.blockerId);
  if (!blocker) {
    throw new Error(`Blocker ${input.blockerId} wurde nicht gefunden.`);
  }

  const now = new Date().toISOString();
  blocker.status = "RESOLVED";
  blocker.resolvedBy = input.resolvedBy;
  blocker.resolutionNote = input.resolutionNote;
  blocker.resolvedAt = now;
  blocker.updatedAt = now;
  next.updatedAt = now;
  addLog(next, input.resolvedBy, "BLOCKER_RESOLVED", `Blocker ${input.blockerId} wurde gelöst.`);
  return next;
}
