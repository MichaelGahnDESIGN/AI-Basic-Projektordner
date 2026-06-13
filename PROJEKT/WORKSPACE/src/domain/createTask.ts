import { addLog } from "./addLog.ts";
import { cloneState } from "./cloneState.ts";
import { nextId } from "./id.ts";
import type { AgentCommsState, CreateTaskInput } from "./types.ts";

export function createTask(state: AgentCommsState, input: CreateTaskInput): AgentCommsState {
  const next = cloneState(state);
  const now = new Date().toISOString();
  const taskId = nextId(next.counters, "task");

  next.tasks.push({
    id: taskId,
    createdAt: now,
    updatedAt: now,
    sender: input.sender,
    recipient: input.recipient,
    type: input.type,
    priority: input.priority,
    status: "PENDING",
    context: input.context,
    task: input.task,
    acceptanceCriteria: input.acceptanceCriteria,
    safetyNote: input.safetyNote
  });
  next.updatedAt = now;
  addLog(next, input.sender, "TASK_CREATED", `Aufgabe ${taskId} für ${input.recipient} erstellt.`);
  return next;
}
