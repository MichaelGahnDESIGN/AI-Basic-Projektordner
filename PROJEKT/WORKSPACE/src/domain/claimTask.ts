import { addLog } from "./addLog.ts";
import { cloneState } from "./cloneState.ts";
import { findActiveTask } from "./findTask.ts";
import type { AgentCommsState, ClaimTaskInput } from "./types.ts";

export function claimTask(state: AgentCommsState, input: ClaimTaskInput): AgentCommsState {
  const next = cloneState(state);
  const task = findActiveTask(next, input.taskId);
  if (task.status !== "PENDING" && task.status !== "BLOCKED") {
    throw new Error(`Aufgabe ${input.taskId} kann im Status ${task.status} nicht übernommen werden.`);
  }

  const now = new Date().toISOString();
  task.status = "IN_PROGRESS";
  task.claimedBy = input.agent;
  task.claimedAt = now;
  task.updatedAt = now;
  next.updatedAt = now;
  addLog(next, input.agent, "TASK_CLAIMED", `Aufgabe ${input.taskId} wurde übernommen.`);
  return next;
}
