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
docker compose exec php-fpm php phing tests                   # PHPUnit unit + functional + smoke, and Jest; prepares demo data
docker compose exec php-fpm php phing tests-unit              # PHPUnit + Jest unit tests
docker compose exec php-fpm php phing tests-functional        # functional only

# Console
docker compose exec php-fpm php bin/console <command>
```

For the full list of Phing targets: `docker compose exec php-fpm php phing -l`.

### Targeted backend tests

Choose the owning test config. In a standalone project, Docker mounts host `app/`
as the `php-fpm` working directory `/var/www/html`; do not repeat `app/` in container
paths. For an application PHPUnit test:

```bash
docker compose exec php-fpm vendor/bin/phpunit --configuration phpunit.xml --testsuite <Suite> --filter <testMethodOrClassName>
```

| Layer | Suite in `app/phpunit.xml` |
|---|---|
| PHP unit | `Unit` |
| Application integration | `Functional` |
| GraphQL API B2C/default | `FrontendApiFunctional` |
| GraphQL API B2B | `FrontendApiFunctionalB2b` |
| Routes, product forms and feed smoke checks | `Smoke` |
| Page and feed performance measurements | `Performance` |

Performance is not part of `phing tests`. `php phing tests-performance` (inside
`php-fpm`) expects prepared performance data; `php phing tests-performance-run`
prepares the test database/Elasticsearch and runs warmup and measurements. Inspect
the targets before use: preparation changes test data and is not a routine targeted check.

Administration and JS tooling use Jest, not storefront Vitest:

```bash
docker compose exec php-fpm php phing tests-unit-jest
docker compose exec php-fpm npm run tests:unit -- --runTestsByPath assets/js/commands/translations/parseFile.test.js
```

In the monorepo, the container mounts the whole repository instead: use
`--configuration project-base/app/phpunit.xml` for application PHPUnit tests and
`npm --prefix project-base/app` for application Jest. Read the root command skill
for package-specific additions.

## Storefront (inside the `storefront` container)

```bash
docker compose exec storefront pnpm run dev          # dev server (port 3000)
docker compose exec storefront pnpm run build         # production build
docker compose exec storefront pnpm run check--fix    # fix TypeScript + linter/formatter
docker compose exec storefront pnpm run typecheck     # TypeScript only
docker compose exec storefront pnpm run gql           # regenerate GraphQL types from schema
docker compose exec storefront pnpm run test          # Vitest watch mode
docker compose exec storefront pnpm run test--no-watch # one complete Vitest run (also used in CI)
docker compose exec storefront pnpm run test--no-watch vitest/components/Blocks/Popup/LoginPopup.test.tsx # targeted run
docker compose exec storefront pnpm run test--update  # update Vitest text/DOM snapshots in watch mode
docker compose exec storefront pnpm run test--no-watch vitest/components/ExtendedNextLink/ExtendedNextLink.test.tsx -u # targeted snapshot update
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
- **Verification** → select only the checks appropriate to the requested scope. This catalog is not authorization to run full preflight or regenerate snapshots; follow the user's execution and approval rules.

## Acceptance / E2E tests

The user runs Cypress manually; do not execute these commands yourself. `base`
generates references, it does not validate them. For the monorepo, keep reference
generation in CI and follow `cypress-tests` for the review/regression workflow.

```bash
make run-acceptance-tests-base     # base Cypress acceptance suite
make open-acceptance-tests-base    # open Cypress GUI for debugging
```

For layer selection, see `.agents/skills/test-writing/SKILL.md`; use `backend-tests`
for PHP and admin Jest, or `storefront-tests` for Vitest and Cypress routing.
