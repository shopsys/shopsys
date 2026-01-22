# CLAUDE.md

## Architecture

Shopsys Platform: monorepo e-commerce platform

- Backend: Symfony PHP + PostgreSQL + Redis + Elasticsearch
- Frontend Admin: Twig templates + LESS/JS
- Storefront: Next.js/React + TypeScript + GraphQL (pnpm)

## Critical Rules

### Package-First Development

**All new PHP business logic MUST go in `/packages/`, NOT `/project-base/`.**

Think: "Can other Shopsys projects reuse this?" → Yes = `/packages/`, No = `/project-base/`

### Docker Commands

NEVER start/stop Docker containers yourself - ask user.

```bash
# PHP/Backend (run IN Docker)
docker compose exec php-fpm php phing <target>
docker compose exec php-fpm composer install

# Storefront (run IN Docker)
docker compose exec storefront pnpm run <script>

# Git/Make/System (run OUTSIDE Docker)
git status && make check-fix

Code Visibility Rules
┌──────────────────────────────────┬────────────┬───────────────────────────┬─────────────────┐
│             Location             │ Visibility │           Types           │      Final      │
├──────────────────────────────────┼────────────┼───────────────────────────┼─────────────────┤
│ /project-base/                   │ private    │ Full typehints everywhere │ Use final       │
├──────────────────────────────────┼────────────┼───────────────────────────┼─────────────────┤
│ /packages/ entities/data objects │ protected  │ NO typehints at all       │ Never use final │
├──────────────────────────────────┼────────────┼───────────────────────────┼─────────────────┤
│ /packages/ FormTypes             │ protected  │ Full typehints            │ MUST use final  │
└──────────────────────────────────┴────────────┴───────────────────────────┴─────────────────┘
Common Phing Targets

build-demo-dev-quick  # Quick dev build with demo data
standards-fix         # Fix coding standards
phpstan               # Static analysis
tests-unit            # Unit tests only
db-migrations         # Run migrations

Workflow

1. Edit code
2. docker compose exec php-fpm php phing standards-fix phpstan
3. make check-fix before committing
4. After GraphQL changes: make generate-schema

Testing

# Backend
docker compose exec php-fpm php phing tests-unit

# Storefront
docker compose exec storefront pnpm run test

# Acceptance (on host)
make run-acceptance-tests-base

Test injection: Use @inject annotation, NOT $this->getContainer()->get()

DocBlocks

ALWAYS specify array contents:
/** @param array<string, Role> $roles */

Use {@inheritdoc} when interface docs are sufficient.

macOS Users

Use ./scripts/mutagen-up.sh and ./scripts/mutagen-down.sh for file sync.
```
