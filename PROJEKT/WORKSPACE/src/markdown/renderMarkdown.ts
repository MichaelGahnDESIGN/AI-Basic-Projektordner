import { normalizeMarkdownText } from "./escapeMarkdown.ts";
import { renderList } from "./renderList.ts";
import type { AgentCommsState, Blocker, Decision, Handoff, Task } from "../domain/types.ts";

export function renderMarkdown(state: AgentCommsState): string {
  return [
    "# Agent Comms",
    "",
    `Projekt: ${state.projectName}`,
    `Aktualisiert: ${state.updatedAt}`,
    "",
    "## Aufgaben-Queue",
    renderTasks(state.tasks),
    "",
    "## DONE",
    renderTasks(state.done),
    "",
    "## Blocker",
    renderBlockers(state.blockers),
    "",
    "## Entscheidungen",
    renderDecisions(state.decisions),
    "",
    "## Übergaben",
    renderHandoffs(state.handoffs),
    "",
    "## Chat",
    renderChat(state),
    "",
    "## Log / History",
    renderLogs(state)
  ].join("\n");
}

function renderTasks(tasks: Task[]): string {
  if (tasks.length === 0) {
    return "Keine Aufgaben vorhanden.";
  }
  return tasks.map(renderTask).join("\n\n");
}

function renderTask(task: Task): string {
  const lines = [
    `### ${task.id} - ${task.status}`,
    "",
    `- Typ: ${task.type}`,
    `- Priorität: ${task.priority}`,
    `- Von: ${task.sender}`,
    `- An: ${task.recipient}`,
    task.claimedBy ? `- Übernommen von: ${task.claimedBy}` : "- Übernommen von: -",
    `- Kontext: ${normalizeMarkdownText(task.context)}`,
    "",
    "**Aufgabe:**",
    normalizeMarkdownText(task.task),
    "",
    "**Akzeptanzkriterien:**",
    renderList(task.acceptanceCriteria),
    "",
    `**Sicherheitsnotiz:** ${normalizeMarkdownText(task.safetyNote)}`
  ];

  if (task.resultNote) {
    lines.push("", "**Ergebnis:**", normalizeMarkdownText(task.resultNote));
  }
  if (task.files && task.files.length > 0) {
    lines.push("", "**Dateien:**", renderList(task.files));
  }
  if (task.tests && task.tests.length > 0) {
    lines.push("", "**Tests:**", renderList(task.tests));
  }
  if (task.commit) {
    lines.push("", `**Commit:** ${task.commit}`);
  }
  if (task.pullRequest) {
    lines.push("", `**PR:** ${task.pullRequest}`);
  }

  return lines.join("\n");
}

function renderBlockers(blockers: Blocker[]): string {
  if (blockers.length === 0) {
    return "Keine Blocker dokumentiert.";
  }
  return blockers.map((blocker) => [
    `- ${blocker.id} [${blocker.status}] ${normalizeMarkdownText(blocker.message)}`,
    `  - Gemeldet von: ${blocker.reporter}`,
    blocker.relatedTaskId ? `  - Aufgabe: ${blocker.relatedTaskId}` : "",
    blocker.resolutionNote ? `  - Lösung: ${normalizeMarkdownText(blocker.resolutionNote)}` : ""
  ].filter(Boolean).join("\n")).join("\n");
}

function renderDecisions(decisions: Decision[]): string {
  if (decisions.length === 0) {
    return "Keine Entscheidungen dokumentiert.";
  }
  return decisions.map((decision) => `- ${decision.id}: ${decision.title} (${decision.author})\n  - ${normalizeMarkdownText(decision.rationale)}`).join("\n");
}

function renderHandoffs(handoffs: Handoff[]): string {
  if (handoffs.length === 0) {
    return "Keine Übergaben vorhanden.";
  }
  return handoffs.map((handoff) => [
    `### ${handoff.id}: ${handoff.from} -> ${handoff.to}`,
    normalizeMarkdownText(handoff.summary),
    "",
    "**Nächste Schritte:**",
    renderList(handoff.nextSteps),
    "",
    `**Verknüpfte Aufgaben:** ${handoff.relatedTaskIds.join(", ") || "-"}`
  ].join("\n")).join("\n\n");
}

function renderChat(state: AgentCommsState): string {
  if (state.chat.length === 0) {
    return "Keine Chat-Nachrichten vorhanden.";
  }
  return state.chat.map((message) => `- ${message.createdAt} | ${message.sender} | ${message.kind}: ${normalizeMarkdownText(message.message)}`).join("\n");
}

function renderLogs(state: AgentCommsState): string {
  const logs = state.logs.map((log) => `- ${log.createdAt} | ${log.actor} | ${log.event}: ${normalizeMarkdownText(log.message)}`);
  const history = state.history.map((entry) => `- ${entry.createdAt} | ${entry.id}: ${normalizeMarkdownText(entry.summary)}`);
  if (logs.length === 0 && history.length === 0) {
    return "Keine Log- oder History-Einträge vorhanden.";
  }
  return [...logs, ...history].join("\n");
}
