import { mkdir, readFile, writeFile } from "node:fs/promises";
import { dirname, join } from "node:path";
import { createInitialState } from "../domain/createInitialState.ts";
import type { AgentCommsState } from "../domain/types.ts";
import { renderMarkdown } from "../markdown/renderMarkdown.ts";

export interface FileStorageOptions {
  baseDir: string;
  markdownFileName: string;
  stateFileName: string;
}

export class FileStorage {
  private readonly options: FileStorageOptions;
  private readonly markdownPath: string;
  private readonly statePath: string;

  constructor(options: FileStorageOptions) {
    this.options = options;
    this.markdownPath = join(options.baseDir, options.markdownFileName);
    this.statePath = join(options.baseDir, options.stateFileName);
  }

  async load(): Promise<AgentCommsState> {
    await mkdir(this.options.baseDir, { recursive: true });
    try {
      return JSON.parse(await readFile(this.statePath, "utf8")) as AgentCommsState;
    } catch (error) {
      if (isMissingFile(error)) {
        const state = createInitialState();
        await this.save(state);
        return state;
      }
      throw error;
    }
  }

  async save(state: AgentCommsState): Promise<void> {
    await mkdir(dirname(this.statePath), { recursive: true });
    await writeFile(this.statePath, `${JSON.stringify(state, null, 2)}\n`, "utf8");
    await writeFile(this.markdownPath, `${renderMarkdown(state)}\n`, "utf8");
  }

  async readMarkdown(): Promise<string> {
    return readFile(this.markdownPath, "utf8");
  }
}

function isMissingFile(error: unknown): boolean {
  return typeof error === "object" && error !== null && "code" in error && error.code === "ENOENT";
}
