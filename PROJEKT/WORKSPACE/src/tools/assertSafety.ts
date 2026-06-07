import { validateSafety } from "../safety/validateSafety.ts";

export function assertSafety(input: unknown): void {
  const result = validateSafety(JSON.stringify(input));
  if (!result.isSafe) {
    throw new Error(`Safety-Check blockiert den Schreibvorgang: ${result.findings.map((finding) => finding.ruleId).join(", ")}`);
  }
}
