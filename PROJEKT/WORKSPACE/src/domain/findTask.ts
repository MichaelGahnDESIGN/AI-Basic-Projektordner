import type { AgentCommsState, Task } from "./types.ts";

export function findActiveTask(state: AgentCommsState, taskId: string): Task {
  const task = state.tasks.find((candidate) => candidate.id === taskId);
  if (!task) {
    throw new Error(`Aufgabe ${taskId} wurde nicht in der aktiven Queue gefunden.`);
  }
  return task;
}
