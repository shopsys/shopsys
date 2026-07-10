---
name: shopsys-commands
description: The command catalog for this Shopsys project — how to build, run, test, check code, access the database, and sync the GraphQL schema, including the macOS Mutagen workflow. Read it when you need the exact command for a task and which run inside Docker containers versus on the host.
---

> **Monorepo note:** if the repository has top-level `packages/` and `project-base/` directories (the shopsys/shopsys monorepo layout, not a standalone project), also read `.agents/skills/monorepo-vs-project/SKILL.md` and apply its delta (package-first + path/role remap) on top of this skill.

# Shopsys commands

**Golden rule:** PHP / Composer / Phing and storefront / pnpm commands run **inside Docker containers**; git, `make`, and system commands run **on the host**. Never start or stop containers yourself — if they aren't running, ask the user to start them.

## Docker environment

- **macOS** — use the helper scripts `./scripts/mutagen-up.sh` / `./scripts/mutagen-down.sh`, or the manual Mutagen workflow below.
- **Linux / Windows** — plain `docker compose`.

```bash
# macOS — start/stop everything (recommended)
./scripts/mutagen-up.sh      # sidecar + mutagen + containers
./scripts/mutagen-down.sh    # stop everything

# macOS — manual Mutagen workflow
docker compose --profile mutagen up -d   # start sidecar containers first
mutagen project start                    # start file sync
mutagen sync list                        # wait for "Watching"
docker compose up -d                     # start remaining containers
mutagen project terminate                # stop file sync (before stopping containers)
docker compose --profile mutagen down    # stop all containers
```

Services: main app `http://127.0.0.1:8000` · admin `http://127.0.0.1:8000/admin` · storefront `http://127.0.0.1:3000` · Adminer `http://127.0.0.1:1100`.

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
docker compose exec storefront pnpm run test--update  # update Vitest snapshots
```

## Host (git / make)

```bash
make check-fix          # run all backend + storefront checks & fixes (orchestrates Docker internally)
make php-checks         # backend only — coding standards + PHPStan
make storefront-checks  # storefront only — JS/TS checks
make generate-schema    # sync the GraphQL schema between backend and storefront
git status              # git always runs on the host
```

## Database access (direct `docker exec`, not compose)

```bash
docker exec shopsys-framework-postgres psql -U root -d shopsys -c "\dt"              # list tables
docker exec shopsys-framework-postgres psql -U root -d shopsys -c "\d table_name"    # describe table
docker exec shopsys-framework-postgres psql -U root -d shopsys -c "SELECT * FROM t;" # query
```

PostgreSQL credentials are in `app/.env`.

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
