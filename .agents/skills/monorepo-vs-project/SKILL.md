---
name: monorepo-vs-project
description: How to read the project-authored skills and docs when working inside the shopsys/shopsys monorepo. Read this whenever a skill or AGENTS.md tells you to "apply the monorepo-vs-project delta". Covers package-first architecture and the path/role remapping between a standalone Shopsys project and this monorepo.
---

# Reading project-authored skills inside the monorepo

Several skills in this repo are **authored from the perspective of a standalone Shopsys project** (a clone of `project-base`, where the platform is an installed read-only dependency). Their canonical copies live under `project-base/.agents/skills/<name>/SKILL.md`. A monorepo skill of the same name is a thin stub that points here.

When a stub says "read the canonical, then apply this delta", translate the canonical's assumptions with the rules below.

## Rules

1. **Package-first — where new code goes inverts.**
   A project puts its code in `app/src/…` because it only *consumes* the packages. **In the monorepo, new business logic goes in `packages/`** (framework and bundles) — that is the primary, editable source. `project-base/` here is the thin sample-project layer: configuration and rare project-specific extensions only. See root `AGENTS.md` → *Monorepo Architecture*.

2. **`app/…` → `project-base/app/…`.**
   Any path the canonical writes relative to the repo root as `app/…` or `storefront/…` is actually under `project-base/` here. Example: the canonical's `app/config/services.yaml` means `project-base/app/config/services.yaml`.

3. **`vendor/shopsys/<pkg>` is `packages/<pkg>` — and it's editable.**
   Where the canonical says "framework reference, read-only, in `vendor/shopsys/<pkg>/src/`", the monorepo equivalent is `packages/<pkg>/src/` — the actual source, which you may modify. There is no read-only `vendor/shopsys/` framework layer here; it *is* `packages/`.

4. **Documentation research → use the `docs-researcher` skill.**
   Where a canonical points to docs.shopsys.com (or "the project's `docs/`") for concepts and guides, the monorepo carries the documentation source locally under `docs/`. Use `.agents/skills/docs-researcher/SKILL.md` to search it — it's faster and version-exact — instead of the public site.

## Applying it

- Read the canonical skill for the *method* (what to do, how to structure output, what patterns to look for).
- Substitute paths per rules 2–3 and priority per rule 1 as you execute.
- Follow the monorepo's own conventions: root `AGENTS.md` (Package-First + the always-on rules), `.agents/skills/coding-conventions/SKILL.md` (visibility/typing per folder, docblocks), and `.agents/skills/shopsys-commands/SKILL.md` (Docker/Phing/pnpm, per-package tests).

That's the whole delta — it lives here once so the individual stubs don't repeat it.
