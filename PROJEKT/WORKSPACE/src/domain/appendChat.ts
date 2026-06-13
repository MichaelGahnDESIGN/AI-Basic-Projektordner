import { addLog } from "./addLog.ts";
import { cloneState } from "./cloneState.ts";
import { nextId } from "./id.ts";
import type { AgentCommsState, ChatKind } from "./types.ts";

export interface AppendChatInput {
  sender: string;
  kind: ChatKind;
  message: string;
}

export function appendChat(state: AgentCommsState, input: AppendChatInput): AgentCommsState {
  const next = cloneState(state);
  const now = new Date().toISOString();
  const chatId = nextId(next.counters, "chat");
  next.chat.push({ id: chatId, createdAt: now, sender: input.sender, kind: input.kind, message: input.message });
  next.updatedAt = now;
  addLog(next, input.sender, "CHAT_APPENDED", `Chat-Nachricht ${chatId} ergänzt.`);
  return next;
}
