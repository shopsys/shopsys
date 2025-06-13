# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Architecture Overview

Shopsys Platform is a **monorepo-based e-commerce platform** with a three-tier architecture:

### Monorepo Structure

- **Framework packages** (glass-box): `/packages/` - Reusable framework bundles and components
- **Project-base** (open-box): `/project-base/` - Customizable application foundation with Symfony backend
- **Storefront** (React/Next.js): `/project-base/storefront/` - Modern React frontend with TypeScript

### Technology Stack

- **Backend**: Symfony PHP application with PostgreSQL, Redis, Elasticsearch
- **Frontend Admin**: Server-rendered Twig templates with LESS/CSS and JavaScript
- **Storefront**: Next.js/React with TypeScript, Tailwind CSS, GraphQL, pnpm
- **API**: GraphQL Frontend API for backend-storefront communication

## Essential Development Commands

### Docker Development Environment (Recommended)

**Choose the appropriate command based on your setup:**

#### Option 1: Standard Docker (Linux/Windows or macOS without Mutagen)

```bash
# Start containers
docker compose up -d

# Check container status
docker compose ps

# Stop containers
docker compose down
```

#### Option 2: Mutagen (macOS only, for better file sync performance)

```bash
# Start containers with mutagen
mutagen-compose up -d

# Check container status
mutagen-compose ps

# Stop containers
mutagen-compose down
```

**How to determine which to use:**

- Check if `x-mutagen` section exists in the root `docker-compose.yml` file
- If `x-mutagen` is present → use `mutagen-compose` commands (macOS only)
- If `x-mutagen` is not present → use `docker compose` commands

```bash
# Access services (same for both options)
# - Main app: http://127.0.0.1:8000
# - Admin: http://127.0.0.1:8000/admin
# - Adminer: http://127.0.0.1:1100
```

**Important Rules for Claude Code**:

- Always check if Docker containers are running first with `docker compose ps` or `mutagen-compose ps`
- If containers are not running and you need to execute commands, ask the user to start them manually instead of starting them yourself
- Determine the correct command (docker compose vs mutagen-compose) by checking for `x-mutagen` section in root docker-compose.yml
- **For ALL container commands:** Use `docker exec` with standard Docker, or `mutagen-compose exec` with Mutagen setups

### Backend Development (Phing)

```bash
# Quick development builds
php phing build-demo-dev-quick    # Quick dev build with demo data, skip tests
php phing build-dev-quick         # Quick build preserving existing DB

# Database management
php phing db-create              # Create database
php phing demo-data              # Load demo data
php phing db-migrations          # Run migrations
php phing db-migrations-generate # Generate new migration

# Asset builds
php phing npm-dev               # Development assets with source maps
php phing npm-watch             # Watch for changes and rebuild
php phing npm-build             # Production assets

# Code quality
php phing standards-fix         # Fix all coding standards issues
php phing phpstan              # Static analysis
```

### Storefront Development (in /project-base/storefront/)

```bash
# Development
pnpm run dev                   # Start dev server (port 3000)
pnpm run build                 # Production build

# Code quality
pnpm run check--fix            # Fix TypeScript + ESLint + Prettier issues
pnpm run lint--fix             # Fix ESLint + format issues
pnpm run typecheck            # Check TypeScript

# GraphQL
pnpm run gql                  # Generate GraphQL types from schema
```

### Testing Commands

```bash
# Backend testing
php phing tests                # All backend tests (unit, functional, smoke)
php phing tests-unit           # Unit tests only
php phing tests-functional     # Functional tests only

# Acceptance testing (Cypress)
make run-acceptance-tests-base    # Run all acceptance tests
make open-acceptance-tests-base   # Open Cypress GUI for debugging

# Storefront testing
cd project-base/storefront
pnpm run test                  # Vitest unit tests
pnpm run test--update          # Update test snapshots
```

### Development Workflow Commands

```bash
# Full quality check and fix workflow
make check-fix                 # Run all checks and fixes for backend + storefront

# Individual checks
make php-checks               # Backend standards + static analysis
make storefront-checks        # Frontend linting + formatting

# GraphQL schema sync
make generate-schema          # Generate and sync GraphQL schema between backend and storefront
```

### Database Access Commands

**Standard Docker:**

```bash
# Direct PostgreSQL access (when containers are running)
docker exec shopsys-framework-postgres psql -U root -d shopsys -c "SELECT * FROM table_name;"

# Common database queries
docker exec shopsys-framework-postgres psql -U root -d shopsys -c "\dt"  # List all tables
docker exec shopsys-framework-postgres psql -U root -d shopsys -c "\d table_name"  # Describe table structure
docker exec shopsys-framework-postgres psql -U root -d shopsys -c "\l"  # List databases
```

**Mutagen (use these commands if your setup has x-mutagen):**

```bash
# Direct PostgreSQL access (when containers are running)
mutagen-compose exec postgres psql -U root -d shopsys -c "SELECT * FROM table_name;"

# Common database queries
mutagen-compose exec postgres psql -U root -d shopsys -c "\dt"  # List all tables
mutagen-compose exec postgres psql -U root -d shopsys -c "\d table_name"  # Describe table structure
mutagen-compose exec postgres psql -U root -d shopsys -c "\l"  # List databases
```

Login credentials for PostgreSQL are set in the `project-base/app/.env` file.

## Project Structure Guidance

### Backend Code Organization

- **Entities**: `/project-base/src/Model/` - Domain entities and business logic
- **Controllers**: `/project-base/src/Controller/` - Web controllers for admin and frontend
- **Forms**: `/project-base/src/Form/` - Symfony form types
- **Templates**: `/project-base/templates/` - Twig templates
- **Migrations**: `/project-base/src/Migrations/` - Database migrations
- **Tests**: `/project-base/tests/` - PHPUnit tests organized by type

### Storefront Code Organization

- **Pages**: `/project-base/storefront/pages/` - Next.js page components
- **Components**: `/project-base/storefront/components/` - Reusable React components
- **GraphQL**: `/project-base/storefront/graphql/` - GraphQL queries and generated types
- **Types**: `/project-base/storefront/types/` - TypeScript type definitions
- **Utils**: `/project-base/storefront/utils/` - Utility functions and hooks

### Framework Package Structure

- **Framework**: `/packages/framework/` - Core platform functionality
- **Frontend API**: `/packages/frontend-api/` - GraphQL API package
- **Other packages**: Additional framework components in `/packages/`

## Development Workflow

1. **Environment Setup**: Use Docker with `docker compose up -d`
2. **Backend Changes**: Edit code → `php phing standards-fix` → `php phing tests`
3. **Frontend Changes**: Edit code → `pnpm run check--fix` → `pnpm run test`
4. **GraphQL Changes**: After backend schema changes → `make generate-schema`
5. **Full Validation**: `make check-fix` before committing

## Testing Strategy

- **Backend**: PHPUnit with unit, functional, and smoke test suites
- **Frontend**: Vitest for unit tests, Cypress for acceptance tests
- **API**: Frontend API tests in `/project-base/tests/FrontendApiBundle/`
- **Acceptance**: Cypress tests cover end-to-end user workflows

## Key Configuration Files

- **Docker**: `/docker-compose.yml`, `/docker/`
- **Build**: `/project-base/app/build.xml` (Phing), `/project-base/app/webpack.config.js`
- **Dependencies**: `/composer.json`, `/project-base/storefront/package.json`
- **Code Quality**: `/ecs.php`, `/phpstan.neon`, `/project-base/storefront/eslint.config.mjs`
- **Database**: Doctrine ORM with PostgreSQL, migrations in `/project-base/src/Migrations/`

## Important Development Notes

- Always use `make generate-schema` after GraphQL schema changes to sync backend and storefront
- The platform supports multi-domain/multi-language setups by default
- Use `php phing build-demo-dev-quick` for fastest development setup with demo data
- Run `make check-fix` before committing to ensure code quality standards
- Storefront development requires Node.js/pnpm, backend requires PHP/Composer
- Framework packages should only be modified via the monorepo, not directly in `/packages/`
