import type { AgentCommsState } from "./types.ts";

export function readContext(state: AgentCommsState) {
  return {
    projectName: state.projectName,
    updatedAt: state.updatedAt,
    activeTasks: state.tasks,
    openBlockers: state.blockers.filter((blocker) => blocker.status === "OPEN"),
    latestLogs: state.logs.slice(-10),
    latestDecisions: state.decisions.slice(-10),
    latestHandoffs: state.handoffs.slice(-5)
  };
}
