import { safetyRules } from "./rules.ts";
import type { SafetyResult } from "./types.ts";

export function validateSafety(content: string): SafetyResult {
  const findings = safetyRules.flatMap((rule) => {
    const match = content.match(rule.pattern);
    if (!match) {
      return [];
    }
    return [{ ruleId: rule.id, label: rule.label, severity: "BLOCK" as const, sample: maskSample(match[0]) }];
  });

  return { isSafe: findings.length === 0, findings };
}

function maskSample(sample: string): string {
  if (sample.length <= 12) {
    return sample;
  }
  return `${sample.slice(0, 6)}...${sample.slice(-4)}`;
}
