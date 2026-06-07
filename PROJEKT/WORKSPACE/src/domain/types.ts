export type TaskStatus = "PENDING" | "IN_PROGRESS" | "DONE" | "BLOCKED" | "CANCELED";
export type TaskType = "CODE" | "REVIEW" | "PIXEL_ART" | "IMAGE_GENERATION" | "UI_CONCEPT" | "DOCS" | "BRAINSTORM" | "DEPLOY" | "QA" | "SECURITY";
export type TaskPriority = "LOW" | "NORMAL" | "HIGH" | "URGENT";
export type ChatKind = "Hinweis" | "Frage" | "Antwort" | "Status" | "Warnung";

export interface Task {
  id: string;
  createdAt: string;
  updatedAt: string;
  sender: string;
  recipient: string;
  type: TaskType;
  priority: TaskPriority;
  status: TaskStatus;
  context: string;
  task: string;
  acceptanceCriteria: string[];
  safetyNote: string;
  claimedBy?: string;
  claimedAt?: string;
  completedAt?: string;
  resultNote?: string;
  files?: string[];
  tests?: string[];
  commit?: string;
  pullRequest?: string;
}

export interface ChatMessage {
  id: string;
  createdAt: string;
  sender: string;
  kind: ChatKind;
  message: string;
}

export interface Blocker {
  id: string;
  createdAt: string;
  updatedAt: string;
  reporter: string;
  message: string;
  status: "OPEN" | "RESOLVED";
  relatedTaskId?: string;
  resolvedBy?: string;
  resolutionNote?: string;
  resolvedAt?: string;
}

export interface Decision {
  id: string;
  createdAt: string;
  author: string;
  title: string;
  rationale: string;
  relatedTaskId?: string;
}

export interface Handoff {
  id: string;
  createdAt: string;
  from: string;
  to: string;
  summary: string;
  nextSteps: string[];
  relatedTaskIds: string[];
}

export interface LogEntry {
  id: string;
  createdAt: string;
  actor: string;
  event: string;
  message: string;
}

export interface HistoryEntry {
  id: string;
  createdAt: string;
  summary: string;
  archivedTaskIds: string[];
  archivedDoneIds: string[];
}

export interface AgentCommsState {
  version: 1;
  projectName: string;
  updatedAt: string;
  counters: {
    task: number;
    chat: number;
    blocker: number;
    decision: number;
    handoff: number;
    log: number;
    history: number;
  };
  tasks: Task[];
  done: Task[];
  chat: ChatMessage[];
  blockers: Blocker[];
  decisions: Decision[];
  handoffs: Handoff[];
  logs: LogEntry[];
  history: HistoryEntry[];
}

export interface CreateTaskInput {
  sender: string;
  recipient: string;
  type: TaskType;
  priority: TaskPriority;
  context: string;
  task: string;
  acceptanceCriteria: string[];
  safetyNote: string;
}

export interface ClaimTaskInput {
  taskId: string;
  agent: string;
}

export interface CompleteTaskInput {
  taskId: string;
  resultNote: string;
  files: string[];
  tests: string[];
  commit?: string;
  pullRequest?: string;
}
