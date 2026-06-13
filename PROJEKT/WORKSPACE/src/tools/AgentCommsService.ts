import { addBlocker } from "../domain/addBlocker.ts";
import { addDecision } from "../domain/addDecision.ts";
import { appendChat } from "../domain/appendChat.ts";
import { claimTask } from "../domain/claimTask.ts";
import { completeTask } from "../domain/completeTask.ts";
import { createTask } from "../domain/createTask.ts";
import { readContext } from "../domain/readContext.ts";
import { resetRound } from "../domain/resetRound.ts";
import { resolveBlocker } from "../domain/resolveBlocker.ts";
import type { CreateTaskInput, TaskPriority, TaskType } from "../domain/types.ts";
import { writeHandoff } from "../domain/writeHandoff.ts";
import { validateSafety } from "../safety/validateSafety.ts";
import type { FileStorage } from "../storage/FileStorage.ts";
import { assertSafety } from "./assertSafety.ts";
import { asRecord, optionalStringValue, stringArrayValue, stringValue } from "./parseInput.ts";

export class AgentCommsService {
  private readonly storage: FileStorage;

  constructor(storage: FileStorage) {
    this.storage = storage;
  }

  async readContext() {
    return readContext(await this.storage.load());
  }

  async listTasks() {
    return (await this.storage.load()).tasks;
  }

  async createTask(input: unknown) {
    assertSafety(input);
    const record = asRecord(input);
    const taskInput: CreateTaskInput = {
      sender: stringValue(record, "sender"),
      recipient: stringValue(record, "recipient"),
      type: stringValue(record, "type") as TaskType,
      priority: stringValue(record, "priority") as TaskPriority,
      context: stringValue(record, "context"),
      task: stringValue(record, "task"),
      acceptanceCriteria: stringArrayValue(record, "acceptanceCriteria"),
      safetyNote: stringValue(record, "safetyNote")
    };
    const next = createTask(await this.storage.load(), taskInput);
    await this.storage.save(next);
    return next.tasks.at(-1);
  }

  async claimTask(input: unknown) {
    assertSafety(input);
    const record = asRecord(input);
    const taskId = stringValue(record, "taskId");
    const next = claimTask(await this.storage.load(), { taskId, agent: stringValue(record, "agent") });
    await this.storage.save(next);
    return next.tasks.find((task) => task.id === taskId);
  }

  async completeTask(input: unknown) {
    assertSafety(input);
    const record = asRecord(input);
    const taskId = stringValue(record, "taskId");
    const next = completeTask(await this.storage.load(), {
      taskId,
      resultNote: stringValue(record, "resultNote"),
      files: stringArrayValue(record, "files"),
      tests: stringArrayValue(record, "tests"),
      commit: optionalStringValue(record, "commit"),
      pullRequest: optionalStringValue(record, "pullRequest")
    });
    await this.storage.save(next);
    return next.done.find((task) => task.id === taskId);
  }

  async appendChat(input: unknown) {
    assertSafety(input);
    const record = asRecord(input);
    const next = appendChat(await this.storage.load(), { sender: stringValue(record, "sender"), kind: stringValue(record, "kind") as "Hinweis", message: stringValue(record, "message") });
    await this.storage.save(next);
    return next.chat.at(-1);
  }

  async addBlocker(input: unknown) {
    assertSafety(input);
    const record = asRecord(input);
    const next = addBlocker(await this.storage.load(), { reporter: stringValue(record, "reporter"), message: stringValue(record, "message"), relatedTaskId: optionalStringValue(record, "relatedTaskId") });
    await this.storage.save(next);
    return next.blockers.at(-1);
  }

  async resolveBlocker(input: unknown) {
    assertSafety(input);
    const record = asRecord(input);
    const blockerId = stringValue(record, "blockerId");
    const next = resolveBlocker(await this.storage.load(), { blockerId, resolvedBy: stringValue(record, "resolvedBy"), resolutionNote: stringValue(record, "resolutionNote") });
    await this.storage.save(next);
    return next.blockers.find((blocker) => blocker.id === blockerId);
  }

  async addDecision(input: unknown) {
    assertSafety(input);
    const record = asRecord(input);
    const next = addDecision(await this.storage.load(), { author: stringValue(record, "author"), title: stringValue(record, "title"), rationale: stringValue(record, "rationale"), relatedTaskId: optionalStringValue(record, "relatedTaskId") });
    await this.storage.save(next);
    return next.decisions.at(-1);
  }

  async writeHandoff(input: unknown) {
    assertSafety(input);
    const record = asRecord(input);
    const next = writeHandoff(await this.storage.load(), { from: stringValue(record, "from"), to: stringValue(record, "to"), summary: stringValue(record, "summary"), nextSteps: stringArrayValue(record, "nextSteps"), relatedTaskIds: Array.isArray(record.relatedTaskIds) ? stringArrayValue(record, "relatedTaskIds") : [] });
    await this.storage.save(next);
    return next.handoffs.at(-1);
  }

  async validateSafety(input: unknown) {
    return validateSafety(stringValue(asRecord(input), "content"));
  }

  async resetRound(input: unknown) {
    assertSafety(input);
    const record = asRecord(input);
    const next = resetRound(await this.storage.load(), { actor: stringValue(record, "actor"), summary: stringValue(record, "summary") });
    await this.storage.save(next);
    return { history: next.history.at(-1), openBlockers: next.blockers };
  }
}
