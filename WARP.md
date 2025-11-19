# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Architecture Overview

Shopsys Platform is a **monorepo-based e-commerce platform** built on a three-tier architecture:

### Monorepo Structure

- **Framework packages** (`/packages/`): Reusable framework bundles and components (glass-box approach)
- **Project-base** (`/project-base/`): Customizable application foundation with Symfony backend (open-box approach)
- **Storefront** (`/project-base/storefront/`): Modern React frontend with Next.js and TypeScript

### Technology Stack

- **Backend**: Symfony PHP with PostgreSQL, Redis, Elasticsearch, RabbitMQ
- **Frontend Admin**: Server-rendered Twig templates with LESS/CSS and JavaScript
- **Storefront**: Next.js 15 with React 19, TypeScript, Tailwind CSS, GraphQL (URQL), pnpm
- **API**: GraphQL Frontend API for backend-storefront communication
- **Infrastructure**: Docker containers with mutagen-compose on macOS

## Essential Development Commands

### Platform-Specific Docker Commands

**Critical**: Use `mutagen-compose` instead of `docker compose` on macOS. Use `docker compose` on Linux/Windows.

### Backend Development (Symfony/PHP)

All PHP commands must run inside Docker containers:

```bash
# Quick development setup
mutagen-compose exec php-fpm php phing build-demo-dev-quick  # macOS
docker compose exec php-fpm php phing build-demo-dev-quick   # Linux/Windows

# Database management
mutagen-compose exec php-fpm php phing db-create
mutagen-compose exec php-fpm php phing demo-data
mutagen-compose exec php-fpm php phing db-migrations
mutagen-compose exec php-fpm php phing db-migrations-generate

# Code quality and testing
mutagen-compose exec php-fpm php phing standards-fix
mutagen-compose exec php-fpm php phing phpstan
mutagen-compose exec php-fpm php phing tests
mutagen-compose exec php-fpm php phing tests-unit
mutagen-compose exec php-fpm php phing tests-functional

# Asset compilation
mutagen-compose exec php-fpm php phing npm-dev
mutagen-compose exec php-fpm php phing npm-watch
```

### Storefront Development (Next.js/React)

All pnpm commands must run inside Docker containers:

```bash
# Development
mutagen-compose exec storefront pnpm run dev      # Start dev server (port 3000)
mutagen-compose exec storefront pnpm run build    # Production build

# Code quality (recommended before commits)
mutagen-compose exec storefront pnpm run check--fix    # Fix TS + ESLint + Prettier
mutagen-compose exec storefront pnpm run lint--fix     # Fix ESLint + format
mutagen-compose exec storefront pnpm run typecheck     # TypeScript check

# GraphQL code generation
mutagen-compose exec storefront pnpm run gql

# Testing
mutagen-compose exec storefront pnpm run test          # Vitest unit tests
mutagen-compose exec storefront pnpm run test--update  # Update snapshots
```

### Make Commands (Host System)

These orchestrate Docker commands and should run directly on the host:

```bash
# Complete workflow (backend + storefront)
make check-fix                 # Run all checks and fixes
make generate-schema          # Sync GraphQL schema between backend/storefront

# Individual checks
make php-checks               # Backend standards + static analysis
make storefront-checks        # Frontend linting + formatting

# Testing
make run-acceptance-tests-base     # Run Cypress tests headlessly
make open-acceptance-tests-base    # Open Cypress GUI for debugging
make run-smoke-tests              # Run smoke tests
```

### Database Access

```bash
# Direct PostgreSQL access (use docker exec, not compose)
docker exec shopsys-framework-postgres psql -U root -d shopsys -c "\\dt"
docker exec shopsys-framework-postgres psql -U root -d shopsys -c "SELECT * FROM products LIMIT 5;"
```

### Service URLs

- Main application: http://127.0.0.1:8000
- Admin interface: http://127.0.0.1:8000/admin
- Storefront: http://127.0.0.1:3000
- Adminer (DB): http://127.0.0.1:1100
- Redis Commander: http://127.0.0.1:1600

## Code Architecture

### Backend Structure (`/project-base/app/src/`)

- **Model/**: Domain entities and business logic (DDD approach)
- **Controller/**: Web controllers (admin and frontend)
- **FrontendApi/**: GraphQL API resolvers and types
- **Form/**: Symfony form types
- **Migrations/**: Database migrations
- **Component/**: Application components and services
- **Command/**: Console commands

### Storefront Structure (`/project-base/storefront/`)

- **app/**: Next.js App Router pages and layouts
- **components/**: Reusable React components organized by purpose
- **graphql/**: GraphQL queries, mutations, and generated types
- **types/**: TypeScript type definitions
- **utils/**: Utility functions and custom hooks
- **store/**: Zustand state management
- **urql/**: URQL GraphQL client configuration
- **styles/**: Tailwind CSS configuration and global styles

### GraphQL Integration

- Backend generates schema at `project-base/app/schema.graphql`
- Storefront uses GraphQL Code Generator to create TypeScript types
- Run `make generate-schema` after any GraphQL schema changes
- Generated types appear in `storefront/graphql/types.ts`

## Development Workflow

### Making Changes

1. **Never start/stop Docker containers yourself** - ask the user if containers aren't running
2. **Backend changes**: Edit code → `make php-checks` → test locally
3. **Storefront changes**: Edit code → `make storefront-checks` → test locally
4. **GraphQL schema changes**: Edit schema → `make generate-schema` → test integration
5. **Before commits**: Always run `make check-fix` to ensure code quality

### Testing Strategy

- **Unit tests**: PHPUnit (backend), Vitest (storefront)
- **Functional tests**: PHPUnit with database fixtures
- **Acceptance tests**: Cypress end-to-end tests
- **API tests**: GraphQL API integration tests
- Use `@inject` annotation in test services instead of `$this->getContainer()->get()`

### Framework vs Project-Base Code Style

**Framework packages** (`/packages/`):

- Use `protected` visibility (extensible)
- No typehints in entities/data objects
- No `final` keyword (except FormTypes)

**Project-base** (`/project-base/`):

- Use `private` visibility and `final` keyword
- Full typehints everywhere
- Consider this production-ready code

### Key Configuration Files

- **Docker**: `docker-compose.yml`, `/docker/`
- **Backend Build**: `project-base/app/build.xml` (Phing)
- **Storefront Config**: `package.json`, `next.config.js`, `tailwind.config.js`
- **Code Quality**: `ecs.php` (backend), `eslint.config.mjs` (storefront)
- **GraphQL**: `codegen-config.ts`, schema generation via Phing

## Common Development Tasks

### Running a Single Test

```bash
# Backend single test
mutagen-compose exec php-fpm php vendor/bin/phpunit tests/Unit/specific/TestClass.php

# Storefront single test
mutagen-compose exec storefront pnpm run test -- specific.test.tsx
```

### Debugging

- **Backend**: Use Xdebug with Docker port mapping
- **Storefront**: Next.js dev server supports Chrome DevTools on port 9229
- **GraphQL**: Use GraphiQL at the GraphQL endpoint for query testing

### License Management

```bash
make check-licenses  # Verify dependency licenses for both backend and storefront
```

## Important Rules

### Docker Container Rules

- **PHP/Composer/Phing**: Must run in `php-fpm` container
- **pnpm/Node.js**: Must run in `storefront` container
- **Git/Make/System**: Must run on host system
- **Platform**: Use `mutagen-compose` on macOS, `docker compose` elsewhere

### Code Quality Requirements

- All TypeScript must be strict mode compliant
- ESLint errors must be fixed before commits
- PHPStan level 5 compliance required
- Prettier formatting enforced on storefront

### GraphQL Schema Sync

- Always run `make generate-schema` after backend GraphQL changes
- Generated files are committed to version control
- Frontend types are auto-generated - don't edit manually

### Performance Considerations

- Next.js uses bundle splitting with custom chunks (URQL, React, Sentry, etc.)
- Images use custom loader with specific device sizes
- Redis caching for backend performance
- Elasticsearch for product search and filtering
