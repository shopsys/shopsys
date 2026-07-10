# AGENTS.md

Guidance for AI agents working in the **Shopsys Platform monorepo**. This file is a signpost: it carries the architecture and always-on rules inline, and links to skills in `.agents/skills/` for the detail. The skills are plain Markdown — follow the links whether or not your agent supports a "skills" mechanism.

## Architecture Overview

Shopsys Platform is a **monorepo-based e-commerce platform**.

- **Backend**: Symfony PHP application with PostgreSQL, Redis, Elasticsearch
- **Frontend Admin**: Server-rendered Twig templates with LESS/CSS and JavaScript
- **Storefront**: Next.js/React with TypeScript, Tailwind CSS, GraphQL, pnpm
- **API**: GraphQL Frontend API for backend-storefront communication

## Monorepo Architecture

### Package-First Development

**Core Principle**: All new PHP business logic MUST be implemented in `/packages/`, NOT in `/project-base/`.

### Structure

- **`/packages/`** (PRIMARY): Core framework implementation — all PHP classes, business logic, reusable components
    - Framework Bundle (`/packages/framework/`) — main business logic
    - Frontend API (`/packages/frontend-api/`) — GraphQL API
    - Other packages — additional framework components
- **`/project-base/`** (SECONDARY): Application configuration layer
    - Configuration files (`config/`)
    - Rare project-specific extensions of package classes
    - Storefront React app (`storefront/`)

### Decision Guide

**Code goes in `/packages/`**: business logic, entities, facades, repositories, controllers, forms.
**Config goes in `/project-base/`**: service configuration, environment settings, routing.
Think: "Can other Shopsys projects reuse this?" → Yes = `/packages/`, No = `/project-base/`.

> **Note — `project-base/AGENTS.md` and its skills are authored for downstream projects.** `project-base/`
> ships its own `AGENTS.md`, `CLAUDE.md`, and `.agents/skills` to downstream projects (the split publishes
> the `project-base/` subfolder as the standalone `shopsys/project-base` repo). Those are the **canonical,
> project-perspective** copies of the shared skills (codebase-locator/analyzer/pattern-finder,
> research-codebase, create-plan, implement-plan, test-writing, shopsys-architecture, shopsys-commands).
> They say package-first does *not* apply and treat packages as read-only `vendor/shopsys/` — the opposite
> of the rules here. During monorepo development, follow **this** root file; the same-named skills in
> `.agents/skills/` are thin stubs that read the project-base canonical and apply one shared delta,
> `.agents/skills/monorepo-vs-project/SKILL.md`. `project-base/AGENTS.md` carries a scope guard saying the same.

## Always-on rules

- **Package-first** (above): new business logic → `/packages/`; only config and rare extensions → `/project-base/`.
- **Commands run in Docker**: PHP/Composer/Phing and storefront/pnpm run **inside containers**; git/`make`/system commands on the **host**. **Never start or stop containers yourself** — if they aren't running, ask the user. → `.agents/skills/shopsys-commands/SKILL.md`
- **After GraphQL changes, run `make generate-schema`** to sync backend and storefront (CI fails if they drift).
- **Coding conventions** — reuse-first (DRY/KISS), comments explain *why*, docblocks for non-obvious types, and per-folder visibility/typing:
    - `project-base/` and `utils/`: `final`, `private`, typehints & return types everywhere.
    - `packages/`: `protected` (not `private`), no typehints/return types in entities & data objects, no `final` (except FormType, which requires it) — because these are extended in project-base.
  Full detail → `.agents/skills/coding-conventions/SKILL.md`.
- **Multi-domain/multi-language** is supported by default — consider it when changing entities, forms, and the API.

## Key Configuration Files

- **Docker**: `/docker-compose.yml`, `/docker/`
- **Build**: `/project-base/app/build.xml` (Phing), `/project-base/app/webpack.config.js`
- **Dependencies**: `/composer.json`, `/project-base/storefront/package.json`
- **Code Quality**: `/ecs.php`, `/phpstan.neon`, `/project-base/storefront/biome.json`
- **Database**: Doctrine ORM with PostgreSQL, migrations in `/project-base/app/src/Migrations/`

## Skills — the detail lives here

- **Commands** (build, DB, tests, checks, schema, Mutagen) → `.agents/skills/shopsys-commands/SKILL.md`
- **Coding conventions & docblocks** → `.agents/skills/coding-conventions/SKILL.md`
- **Find where code lives / how it works / example patterns** → `.agents/skills/codebase-locator`, `codebase-analyzer`, `codebase-pattern-finder`
- **Deep research · plan · implement · tests** → `.agents/skills/research-codebase`, `create-plan`, `implement-plan`, `test-writing`
- **Reading project-authored skills in the monorepo** (package-first + path remap) → `.agents/skills/monorepo-vs-project/SKILL.md`
- **Upgrade notes**: when asked to generate them, use `/generate-upgrade-notes` instead of analyzing commits by hand.
- **More** — the list above isn't exhaustive; browse `.agents/skills/` for the full set.
