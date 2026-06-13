import { mkdtemp, rm } from "node:fs/promises";
import { tmpdir } from "node:os";
import { join } from "node:path";
import assert from "node:assert/strict";
import { afterEach, describe, it } from "node:test";
import { FileStorage } from "../../src/storage/FileStorage.ts";

const tempDirs: string[] = [];

afterEach(async () => {
  for (const dir of tempDirs.splice(0)) {
    await rm(dir, { recursive: true, force: true });
  }
});

describe("FileStorage", () => {
  it("legt State und Markdown beim Speichern an und kann sie wieder laden", async () => {
    const tempDir = await mkdtemp(join(tmpdir(), "agent-comms-"));
    tempDirs.push(tempDir);
    const storage = new FileStorage({ baseDir: tempDir, markdownFileName: "agent_comms.md", stateFileName: "agent_comms.state.json" });
    const state = await storage.load();
    state.chat.push({ id: "CHAT-0001", createdAt: "2026-06-07T10:00:00.000Z", sender: "Codex", kind: "Hinweis", message: "Lokaler Test." });
    await storage.save(state);
    assert.equal((await storage.load()).chat[0]?.message, "Lokaler Test.");
    assert.match(await storage.readMarkdown(), /Lokaler Test\./);
  });
});
