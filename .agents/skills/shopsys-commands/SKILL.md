---
name: shopsys-commands
description: The command catalog for the Shopsys monorepo — how to build, run, test, check code, access the database, and sync the GraphQL schema, including the macOS Mutagen workflow and per-package test configs. Read it when you need the exact command for a task and which run inside Docker containers versus on the host.
---

# shopsys-commands (monorepo)

The command catalog is authored from the project perspective and is almost identical here. **Read the canonical:**

- `project-base/.agents/skills/shopsys-commands/SKILL.md`

Then apply the monorepo delta (`.agents/skills/monorepo-vs-project/SKILL.md`). For commands, the only monorepo additions are:

- **Package unit tests use that package's own config:** `vendor/bin/phpunit --configuration packages/<package-name>/phpunit.xml --filter <name>` (project-base suites still use `--configuration project-base/app/phpunit.xml`).
- **`packages/` is editable source** — the same Docker/Phing/pnpm/Make commands apply; only the code you're building lives in `packages/` rather than `app/` (path remap per the delta). The Mutagen workflow, service URLs, and DB access are identical.
