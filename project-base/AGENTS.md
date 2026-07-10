# AGENTS.md

Guidance for AI agents working in this **Shopsys Platform project** (a repository created from `shopsys/project-base`). This file is a signpost: it carries a few always-on rules inline and links to skills in `.agents/skills/` for everything deeper. The skills are plain Markdown — follow the links whether or not your agent supports a "skills" mechanism.

## ⚠️ SCOPE — read first

This file applies **only when this repository is the standalone root of a Shopsys project** — you cloned `shopsys/project-base`, and the `shopsys/*` framework is an installed read-only dependency under `vendor/shopsys/`.

**If a sibling `../packages/` directory and a root `../AGENTS.md` exist one level up, you are inside the `shopsys/shopsys` monorepo, not a project.** Ignore this file and follow the monorepo's root `AGENTS.md` — its rules are the correct ones there and deliberately contradict this file.

## What this repository is

A project built on Shopsys Platform: Symfony/PHP backend (`app/`), Next.js/React storefront (`storefront/`), GraphQL Frontend API between them, PostgreSQL/Redis/Elasticsearch. The framework ships as Composer packages in `vendor/shopsys/` — you **consume and extend** them, you don't author them.

## Always-on rules

- **Never edit `vendor/shopsys/`** — it's read-only reference. Change platform behavior by extending it from `app/` (Entity Extension, service/FormType overrides). → `.agents/skills/shopsys-architecture/SKILL.md`
- **Your code conventions:** `final` classes, `private` visibility, typehints and return types everywhere, docblocks for non-obvious types. → `.agents/skills/coding-conventions/SKILL.md`
- **Reuse first (DRY/KISS):** check `vendor/shopsys/` and `app/src/` for existing functionality before writing new code.
- **Commands run in Docker:** PHP/Composer/Phing and storefront/pnpm run inside containers; git/`make` on the host. Never start/stop containers yourself. → `.agents/skills/shopsys-commands/SKILL.md`
- **After GraphQL changes, run `make generate-schema`** so backend and storefront stay in sync (CI fails if they drift).

## Skills — the rest lives here

- **Architecture & how to extend** (where code lives, Entity Extension, vendor overrides, Data/Factory/Facade, docs links) → `.agents/skills/shopsys-architecture/SKILL.md`
- **Commands** (build, DB, tests, checks, schema) → `.agents/skills/shopsys-commands/SKILL.md`
- **Coding conventions & docblocks** → `.agents/skills/coding-conventions/SKILL.md`
- **Find where code lives** → `.agents/skills/codebase-locator/SKILL.md`
- **Understand how code works** → `.agents/skills/codebase-analyzer/SKILL.md`
- **Find example patterns to copy** → `.agents/skills/codebase-pattern-finder/SKILL.md`
- **Deep multi-agent research** → `.agents/skills/research-codebase/SKILL.md`
- **Plan a change** → `.agents/skills/create-plan/SKILL.md`
- **Implement an approved plan** → `.agents/skills/implement-plan/SKILL.md`
- **Write / run / debug tests** → `.agents/skills/test-writing/SKILL.md`
- **More** — the list above isn't exhaustive; browse `.agents/skills/` for the full set (e.g. `web-search-research`, `storefront-pr-review`).
