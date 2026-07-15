---
name: storefront-detect-unnecessary-changes
description: Audits current storefront worktree or branch changes for code and artifacts that are unnecessary, dead, speculative, equivalent, redundant, or unrelated. Use before committing storefront changes, when simplifying a frontend diff, or when the user asks whether anything can be removed from a storefront implementation.
---

# storefront-detect-unnecessary-changes (monorepo)

The canonical instructions live in project-base. **Read them:**

- `project-base/.agents/skills/storefront-detect-unnecessary-changes/SKILL.md`

Then apply the monorepo delta (`.agents/skills/monorepo-vs-project/SKILL.md`). For this skill it means:

- Storefront code the canonical calls `storefront/` is `project-base/storefront/` here.
- Keep package-first and generated-source ownership in mind when tracing a storefront change into backend or shared package code.
