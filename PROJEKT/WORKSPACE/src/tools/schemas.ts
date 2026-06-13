export const emptyObjectSchema = { type: "object", properties: {}, additionalProperties: false };

export function requiredStringProperties(required: string[], optional: string[] = []) {
  const properties: Record<string, unknown> = {};
  for (const name of [...required, ...optional]) {
    properties[name] = { type: "string" };
  }
  return { type: "object", properties, required, additionalProperties: true };
}

export function arrayProperty(description: string) {
  return { type: "array", description, items: { type: "string" } };
}
