export interface SafetyFinding {
  ruleId: string;
  label: string;
  severity: "WARN" | "BLOCK";
  sample: string;
}

export interface SafetyResult {
  isSafe: boolean;
  findings: SafetyFinding[];
}
