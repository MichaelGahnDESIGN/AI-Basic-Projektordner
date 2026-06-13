export interface SafetyRule {
  id: string;
  label: string;
  pattern: RegExp;
}

// Diese Regeln sind bewusst konservativ. Sie blockieren offensichtliche Leaks,
// ersetzen aber keine vollständige Datenschutz- oder DLP-Prüfung.
export const safetyRules: SafetyRule[] = [
  { id: "env-file-reference", label: "Verweis auf echte .env-Dateien oder Umgebungsgeheimnisse", pattern: /(^|[\s/\\])\.env(\.|$|\s)|\b(API_KEY|SECRET_KEY|ACCESS_TOKEN)\b/i },
  { id: "private-key", label: "Privater Schlüssel", pattern: /-----BEGIN (RSA |OPENSSH |EC |DSA )?PRIVATE KEY-----/i },
  { id: "password-assignment", label: "Passwort-Zuweisung", pattern: /\b(passwort|password|pwd)\s*[:=]\s*["']?[^"'\s]{6,}/i },
  { id: "token-assignment", label: "Token-Zuweisung", pattern: /\b(token|bearer|auth_token|access_token|refresh_token)\s*[:=]\s*["']?[A-Za-z0-9._\-]{12,}/i },
  { id: "database-url", label: "Datenbank-Zugangsdaten oder Connection-String", pattern: /\b(postgres|mysql|mongodb|redis):\/\/[^ \n]+/i },
  { id: "ssh-material", label: "SSH-Zugangsdaten oder SSH-Schlüsselhinweis", pattern: /\b(id_rsa|id_ed25519|ssh-rsa|ssh-ed25519)\b/i },
  { id: "dump-reference", label: "Dump- oder Backup-Datei mit möglichem Dateninhalt", pattern: /\b(dump|backup).*\.(sql|sqlite|db|tar|zip)\b/i }
];
