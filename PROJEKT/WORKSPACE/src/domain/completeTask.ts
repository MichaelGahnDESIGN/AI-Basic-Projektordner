import { addLog } from "./addLog.ts";
import { cloneState } from "./cloneState.ts";
import type { AgentCommsState, CompleteTaskInput } from "./types.ts";

export function completeTask(state: AgentCommsState, input: CompleteTaskInput): AgentCommsState {
  const next = cloneState(state);
  const taskIndex = next.tasks.findIndex((task) => task.id === input.taskId);
  if (taskIndex === -1) {
    throw new Error(`Aufgabe ${input.taskId} wurde nicht in der aktiven Queue gefunden.`);
  }

  const task = next.tasks[taskIndex];
  if (!task) {
    throw new Error(`Aufgabe ${input.taskId} konnte nicht gelesen werden.`);
  }

  const now = new Date().toISOString();
  next.tasks.splice(taskIndex, 1);
  next.done.push({
    ...task,
    status: "DONE",
    updatedAt: now,
    completedAt: now,
    resultNote: input.resultNote,
    files: input.files,
    tests: input.tests,
    commit: input.commit,
    pullRequest: input.pullRequest
  });
  next.updatedAt = now;
  addLog(next, task.claimedBy ?? task.sender, "TASK_COMPLETED", `Aufgabe ${input.taskId} wurde abgeschlossen.`);
  return next;
}
