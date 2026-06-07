export function renderList(items: string[]): string {
  if (items.length === 0) {
    return "- Keine Angaben.";
  }
  return items.map((item) => `- ${item}`).join("\n");
}
