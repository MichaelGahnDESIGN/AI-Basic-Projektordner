import { addLog } from "./addLog.ts";
import { cloneState } from "./cloneState.ts";
import { nextId } from "./id.ts";
import type { AgentCommsState } from "./types.ts";

export interface AddDecisionInput {
  author: string;
  title: string;
  rationale: string;
  relatedTaskId?: string;
}

export function addDecision(state: AgentCommsState, input: AddDecisionInput): AgentCommsState {
  const next = cloneState(state);
  const now = new Date().toISOString();
  const decisionId = nextId(next.counters, "decision");
  next.decisions.push({ id: decisionId, createdAt: now, author: input.author, title: input.title, rationale: input.rationale, relatedTaskId: input.relatedTaskId });
  next.updatedAt = now;
  addLog(next, input.author, "DECISION_ADDED", `Entscheidung ${decisionId} dokumentiert.`);
  return next;
}
