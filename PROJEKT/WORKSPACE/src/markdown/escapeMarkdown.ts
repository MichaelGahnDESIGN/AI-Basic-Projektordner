export function normalizeMarkdownText(value: string): string {
  return value.trim().replace(/\r\n/g, "\n");
}
