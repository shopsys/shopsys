---
name: shopsys-commands
description: The command catalog for this Shopsys project — how to build, run, test, check code, and sync the GraphQL schema. Read it when you need the exact command for a task and which are run inside Docker containers versus on the host. Covers Phing targets, pnpm scripts, and Make targets.
---

# Shopsys commands

**Golden rule:** PHP / Composer / Phing and storefront / pnpm commands run **inside Docker containers**; git, `make`, and system commands run **on the host**. Never start or stop containers yourself — if they aren't running, ask the user to start them.

## Backend (inside the `php-fpm` container)

```bash
# Build
docker compose exec php-fpm php phing build-demo-dev-quick   # quick dev build with demo data
docker compose exec php-fpm php phing build-dev-quick        # quick build, preserve existing DB

# Database & migrations
docker compose exec php-fpm php phing db-migrations           # apply migrations
docker compose exec php-fpm php phing db-migrations-generate  # generate a migration from entity changes
docker compose exec php-fpm php phing demo-data               # load demo data

# Code quality
docker compose exec php-fpm php phing standards-fix           # fix coding standards (ECS) — includes annotations-fix
docker compose exec php-fpm php phing annotations-fix         # regenerate @property/@method docblocks for the extension layer (so PHPStan understands extended types)
docker compose exec php-fpm php phing phpstan                 # static analysis

# Tests
docker compose exec php-fpm php phing tests                   # unit + functional + smoke
docker compose exec php-fpm php phing tests-unit              # unit only
docker compose exec php-fpm php phing tests-functional        # functional only

# Console
docker compose exec php-fpm php bin/console <command>
```

For the full list of Phing targets: `docker compose exec php-fpm php phing -l`.

## Storefront (inside the `storefront` container)

```bash
docker compose exec storefront pnpm run dev          # dev server (port 3000)
docker compose exec storefront pnpm run build         # production build
docker compose exec storefront pnpm run check--fix    # fix TypeScript + linter/formatter
docker compose exec storefront pnpm run typecheck     # TypeScript only
docker compose exec storefront pnpm run gql           # regenerate GraphQL types from schema
docker compose exec storefront pnpm run test          # Vitest unit tests
```

## Host (git / make)

```bash
make check-fix          # run all backend + storefront checks & fixes (orchestrates Docker internally)
make generate-schema    # sync the GraphQL schema between backend and storefront
git status              # git always runs on the host
```

## Common workflows

- **After changing the GraphQL schema** — creating/editing `*.types.yaml` type definitions, storefront `.graphql` files, or resolvers → `make generate-schema` so backend and storefront stay in sync. CI fails if they drift.
- **After changing an entity** → `db-migrations-generate`, review the migration, then `db-migrations`.
- **Before committing** → `make check-fix` (and `docker compose exec php-fpm php phing tests` for affected areas).

## Acceptance / E2E tests

```bash
make run-acceptance-tests-base     # base Cypress acceptance suite
make open-acceptance-tests-base    # open Cypress GUI for debugging
```

For writing and debugging tests, see `.agents/skills/test-writing/SKILL.md`.
