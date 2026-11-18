---
name: codebase-pattern-finder
description: Finds similar implementations, usage examples, and established patterns in the Shopsys monorepo to model new work after. Like codebase-locator, but returns concrete code snippets with file:line references, not just locations.
---

# codebase-pattern-finder (monorepo)

The canonical instructions for this skill are authored from the project perspective. **Read them first:**

- `project-base/.agents/skills/codebase-pattern-finder/SKILL.md`

Then apply the monorepo delta (package-first + path/role remap):

- `.agents/skills/monorepo-vs-project/SKILL.md`

In short, for *this* skill the delta means the **primary pattern source inverts**: prefer battle-tested patterns from `packages/*/src/` **first** (the primary, editable framework source — what the canonical calls `vendor/shopsys/<pkg>/src/`), then `project-base/app/…` and `project-base/storefront/…` (what the canonical calls `app/…` and `storefront/…`). Everything else — pattern categories, code-extraction method, output structure — follows the canonical unchanged.
