---
name: codebase-analyzer
description: Analyzes codebase implementation details. Call the codebase-analyzer agent when you need to find detailed information about specific components. As always, the more detailed your request prompt, the better! :)
tools: Read, Grep, Glob, LS
---

# codebase-analyzer (monorepo)

The canonical instructions for this skill are authored from the project perspective. **Read them first:**

- `project-base/.agents/skills/codebase-analyzer/SKILL.md`

Then apply the monorepo delta (package-first + path/role remap):

- `.agents/skills/monorepo-vs-project/SKILL.md`

In short, for *this* skill the delta means the **primary implementations you trace live in `packages/*/src/`** — what the canonical calls `vendor/shopsys/<pkg>/src/` is `packages/<pkg>` here (and it's editable, not read-only). Any `app/…` or `storefront/…` path the canonical uses is under `project-base/…` here. Everything else — analysis method, code-path tracing, output structure — follows the canonical unchanged.
