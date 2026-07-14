---
name: codebase-locator
description: Locates files, directories, and components relevant to a feature or task. Call `codebase-locator` with human language prompt describing what you're looking for. Basically a "Super Grep/Glob/LS tool" — Use it if you find yourself desiring to use one of these tools more than once.
tools: Grep, Glob, LS
---

# codebase-locator (monorepo)

The canonical instructions for this skill are authored from the project perspective. **Read them first:**

- `project-base/.agents/skills/codebase-locator/SKILL.md`

Then apply the monorepo delta (package-first + path/role remap):

- `.agents/skills/monorepo-vs-project/SKILL.md`

In short, for *this* skill the delta means the **search priority inverts**: search `packages/*/src/` **first** (the primary, editable framework source — what the canonical calls `vendor/shopsys/<pkg>/src/`), then `project-base/app/…` and `project-base/storefront/…` (what the canonical calls `app/…` and `storefront/…`, the thin project layer). Group results with the framework/`packages` layer as primary. Everything else — method, naming patterns, output structure — follows the canonical unchanged.
