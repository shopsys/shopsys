---
name: create-plan
description: Creates detailed implementation plans through an interactive research and specification workflow.
---

# create-plan (monorepo)

The canonical instructions for this skill are authored from the project perspective. **Read them first:**

- `project-base/.agents/skills/create-plan/SKILL.md`

Then apply the monorepo delta (package-first + path/role remap):

- `.agents/skills/monorepo-vs-project/SKILL.md`

In short, for *this* skill the delta means: when planning **where new code goes**, apply package-first — new entities/logic go in `packages/*/src/` (the primary, editable framework source), not `project-base`, and only rare project-specific extensions land in `project-base/app/…`. Remap paths as you read the canonical: `vendor/shopsys/<pkg>` → `packages/<pkg>` (editable, not read-only), and `app/…` / `storefront/…` → `project-base/app/…` / `project-base/storefront/…`. Everything else — the interactive workflow, plan template, success-criteria split, Docker commands, and `docs/plans/{name}.md` output path — follows the canonical unchanged.
