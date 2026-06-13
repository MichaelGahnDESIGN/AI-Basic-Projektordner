import assert from "node:assert/strict";
import { describe, it } from "node:test";
import { claimTask } from "../../src/domain/claimTask.ts";
import { completeTask } from "../../src/domain/completeTask.ts";
import { createTask } from "../../src/domain/createTask.ts";
import { createInitialState } from "../../src/domain/createInitialState.ts";

describe("Task-Lebenszyklus", () => {
  it("erstellt, übernimmt und erledigt eine Aufgabe nachvollziehbar", () => {
    const state = createInitialState();
    const withTask = createTask(state, { sender: "Codex", recipient: "Claude", type: "CODE", priority: "HIGH", context: "Phase 1 MCP", task: "Implementiere die Statuswechsel.", acceptanceCriteria: ["Statuswechsel sind getestet."], safetyNote: "Keine Secrets verwenden." });
    assert.equal(withTask.tasks.length, 1);
    assert.equal(withTask.tasks[0]?.id, "TASK-0001");
    assert.equal(withTask.tasks[0]?.status, "PENDING");
    const claimed = claimTask(withTask, { taskId: "TASK-0001", agent: "Claude" });
    assert.equal(claimed.tasks[0]?.status, "IN_PROGRESS");
    assert.equal(claimed.tasks[0]?.claimedBy, "Claude");
    const completed = completeTask(claimed, { taskId: "TASK-0001", resultNote: "Statuswechsel implementiert.", files: ["src/domain/claimTask.ts"], tests: ["npm test"], commit: "abc123" });
    assert.equal(completed.tasks.length, 0);
    assert.equal(completed.done.length, 1);
    assert.equal(completed.done[0]?.status, "DONE");
    assert.match(completed.logs.at(-1)?.message ?? "", /TASK-0001/);
  });

  it("verhindert doppelte Übernahme erledigter Aufgaben", () => {
    const withTask = createTask(createInitialState(), { sender: "Codex", recipient: "Claude", type: "QA", priority: "NORMAL", context: "Tests", task: "Prüfe die Tests.", acceptanceCriteria: ["Tests laufen."], safetyNote: "Keine sensiblen Daten ausgeben." });
    const claimed = claimTask(withTask, { taskId: "TASK-0001", agent: "Claude" });
    const completed = completeTask(claimed, { taskId: "TASK-0001", resultNote: "Erledigt.", files: [], tests: ["npm test"] });
    assert.throws(() => claimTask(completed, { taskId: "TASK-0001", agent: "Codex" }), /Aufgabe TASK-0001 wurde nicht in der aktiven Queue gefunden\./);
  });
});
