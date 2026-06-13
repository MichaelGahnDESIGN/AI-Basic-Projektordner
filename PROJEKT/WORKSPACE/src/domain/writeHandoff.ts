import { addLog } from "./addLog.ts";
import { cloneState } from "./cloneState.ts";
import { nextId } from "./id.ts";
import type { AgentCommsState } from "./types.ts";

export interface WriteHandoffInput {
  from: string;
  to: string;
  summary: string;
  nextSteps: string[];
  relatedTaskIds?: string[];
}

export function writeHandoff(state: AgentCommsState, input: WriteHandoffInput): AgentCommsState {
  const next = cloneState(state);
  const now = new Date().toISOString();
  const handoffId = nextId(next.counters, "handoff");
  next.handoffs.push({ id: handoffId, createdAt: now, from: input.from, to: input.to, summary: input.summary, nextSteps: input.nextSteps, relatedTaskIds: input.relatedTaskIds ?? [] });
  next.updatedAt = now;
  addLog(next, input.from, "HANDOFF_WRITTEN", `Übergabe ${handoffId} an ${input.to} geschrieben.`);
  return next;
}
