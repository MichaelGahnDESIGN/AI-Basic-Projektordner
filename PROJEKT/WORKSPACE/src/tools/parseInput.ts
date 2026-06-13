export function asRecord(input: unknown): Record<string, unknown> {
  if (typeof input !== "object" || input === null || Array.isArray(input)) {
    throw new Error("Tool-Eingabe muss ein Objekt sein.");
  }
  return input as Record<string, unknown>;
}

export function stringValue(input: Record<string, unknown>, key: string): string {
  const value = input[key];
  if (typeof value !== "string" || value.trim() === "") {
    throw new Error(`Pflichtfeld ${key} muss ein nicht-leerer Text sein.`);
  }
  return value.trim();
}

export function optionalStringValue(input: Record<string, unknown>, key: string): string | undefined {
  const value = input[key];
  if (value === undefined || value === null || value === "") {
    return undefined;
  }
  if (typeof value !== "string") {
    throw new Error(`Feld ${key} muss ein Text sein.`);
  }
  return value.trim();
}

export function stringArrayValue(input: Record<string, unknown>, key: string): string[] {
  const value = input[key];
  if (!Array.isArray(value) || value.some((item) => typeof item !== "string")) {
    throw new Error(`Pflichtfeld ${key} muss eine Textliste sein.`);
  }
  return value.map((item) => item.trim()).filter(Boolean);
}
