import assert from "node:assert/strict";
import { describe, it } from "node:test";
import { addBlocker } from "../../src/domain/addBlocker.ts";
import { addDecision } from "../../src/domain/addDecision.ts";
import { appendChat } from "../../src/domain/appendChat.ts";
import { createInitialState } from "../../src/domain/createInitialState.ts";
import { createTask } from "../../src/domain/createTask.ts";
import { renderMarkdown } from "../../src/markdown/renderMarkdown.ts";

describe("renderMarkdown", () => {
  it("rendert Queue, Chat, Blocker und Entscheidungen menschenlesbar", () => {
    let state = appendChat(createInitialState(), { sender: "Codex", kind: "Hinweis", message: "Phase 1 startet lokal." });
    state = createTask(state, { sender: "Codex", recipient: "Claude", type: "DOCS", priority: "NORMAL", context: "Dokumentation", task: "README prüfen.", acceptanceCriteria: ["README erklärt die Nutzung."], safetyNote: "Keine Secrets." });
    state = addBlocker(state, { reporter: "Claude", message: "Unklarer Projektname.", relatedTaskId: "TASK-0001" });
    state = addDecision(state, { author: "Codex", title: "Lokal-first", rationale: "Keine Cloud-Pflicht in Phase 1." });
    const markdown = renderMarkdown(state);
    assert.match(markdown, /# Agent Comms/);
    assert.match(markdown, /## Aufgaben-Queue/);
    assert.match(markdown, /TASK-0001/);
    assert.match(markdown, /## Chat/);
    assert.match(markdown, /Unklarer Projektname/);
    assert.match(markdown, /Lokal-first/);
  });
});
