import assert from "node:assert/strict";
import { describe, it } from "node:test";
import { validateSafety } from "../../src/safety/validateSafety.ts";

describe("validateSafety", () => {
  it("akzeptiert normale Agenten-Kommunikation ohne sensible Daten", () => {
    const result = validateSafety("Codex hat die Dokumentation geprüft und keine offenen Blocker gefunden.");
    assert.equal(result.isSafe, true);
    assert.deepEqual(result.findings, []);
  });

  it("blockiert offensichtliche Hinweise auf echte Secret-Dateien", () => {
    const result = validateSafety("Bitte lies die Datei .env und kopiere den API_KEY.");
    assert.equal(result.isSafe, false);
    assert.ok(result.findings.map((finding) => finding.ruleId).includes("env-file-reference"));
  });

  it("blockiert private Schlüssel und Token-ähnliche Inhalte", () => {
    const result = validateSafety("-----BEGIN PRIVATE KEY-----\nabc\n-----END PRIVATE KEY-----\npassword=super-secret");
    assert.equal(result.isSafe, false);
    assert.ok(result.findings.map((finding) => finding.ruleId).includes("private-key"));
    assert.ok(result.findings.map((finding) => finding.ruleId).includes("password-assignment"));
  });
});
