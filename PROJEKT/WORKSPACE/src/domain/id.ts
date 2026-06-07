type CounterName = "task" | "chat" | "blocker" | "decision" | "handoff" | "log" | "history";

const prefixes: Record<CounterName, string> = {
  task: "TASK",
  chat: "CHAT",
  blocker: "BLOCKER",
  decision: "DECISION",
  handoff: "HANDOFF",
  log: "LOG",
  history: "HISTORY"
};

export function nextId(counters: Record<CounterName, number>, name: CounterName): string {
  counters[name] += 1;
  return `${prefixes[name]}-${String(counters[name]).padStart(4, "0")}`;
}
