---
name: codebase-pattern-finder
description: codebase-pattern-finder is a useful subagent for finding similar implementations, usage examples, or existing patterns that can be modeled after. It will give you concrete code examples based on what you're looking for! It's sorta like codebase-locator, but it will not only tell you the location of files, it will also give you code details!
tools: Grep, Glob, Read, LS
---

# codebase-pattern-finder (monorepo)

The canonical instructions for this skill are authored from the project perspective. **Read them first:**

- `project-base/.agents/skills/codebase-pattern-finder/SKILL.md`

Then apply the monorepo delta (package-first + path/role remap):

- `.agents/skills/monorepo-vs-project/SKILL.md`

In short, for *this* skill the delta means the **primary pattern source inverts**: prefer battle-tested patterns from `packages/*/src/` **first** (the primary, editable framework source — what the canonical calls `vendor/shopsys/<pkg>/src/`), then `project-base/app/…` and `project-base/storefront/…` (what the canonical calls `app/…` and `storefront/…`). Everything else — pattern categories, code-extraction method, output structure — follows the canonical unchanged.
