import { resolve } from "node:path";
import { createServer } from "./mcp/createServer.ts";

const baseDir = resolve(process.env.AGENT_COMMS_DIR ?? process.cwd());
await createServer({ baseDir }).listen();
