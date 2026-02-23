# AGENTS.md

This file provides guidance to agents when working with code in this repository.

## Architecture Overview

Shopsys Platform is a **monorepo-based e-commerce platform** with a three-tier architecture:

### Technology Stack

- **Backend**: Symfony PHP application with PostgreSQL, Redis, Elasticsearch
- **Frontend Admin**: Server-rendered Twig templates with LESS/CSS and JavaScript
- **Storefront**: Next.js/React with TypeScript, Tailwind CSS, GraphQL, pnpm
- **API**: GraphQL Frontend API for backend-storefront communication

## Monorepo Architecture

### Package-First Development

**Core Principle**: All new PHP business logic MUST be implemented in `/packages/`, NOT in `/project-base/`.

### Structure

- **`/packages/`** (PRIMARY): Core framework implementation - All PHP classes, business logic, and reusable components
    - Framework Bundle (`/packages/framework/`) - Main business logic
    - Frontend API (`/packages/frontend-api/`) - GraphQL API
    - Other packages - Additional framework components
- **`/project-base/`** (SECONDARY): Application configuration layer
    - Configuration files (`config/`)
    - Rare project-specific extensions of package classes
    - Storefront React app (`storefront/`)

### Decision Guide

**Code goes in `/packages/`**: Business logic, entities, facades, repositories, controllers, forms
**Config goes in `/project-base/`**: Service configuration, environment settings, routing

Think: "Can other Shopsys projects reuse this?" → Yes = `/packages/`, No = `/project-base/`

## Essential Development Commands

### Docker Development Environment (Recommended)

**Important Rules for Agent**:

- Whenever Docker containers are not running, ALWAYS ask the user to start them manually
- NEVER start Docker containers yourself
- NEVER stop Docker containers yourself
- **Use proper Docker commands for different platforms and tasks**

### Docker Command Guidelines

**Platform-specific Docker Compose:**

- **macOS**: Use helper scripts `./scripts/mutagen-up.sh` and `./scripts/mutagen-down.sh`, or manually use `docker compose --profile mutagen` + `mutagen project start/terminate`
- **Linux/Windows**: Use `docker compose`

**Commands that MUST run in Docker containers:**

- **PHP commands**: `php`, `composer`, Phing targets
- **Storefront/pnpm commands**: For storefront development
- **Application-specific tools**: PHPStan, tests, etc.

**Commands that MUST run OUTSIDE Docker containers:**

- **Git commands**: `git status`, `git commit`, `git push`, etc.
- **System commands**: `bash`, `ls`, `find`, `grep`, etc.
- **Make commands**: `make check-fix`, `make generate-schema`, etc.

**Correct Docker Command Patterns:**

```bash
# Backend/PHP commands
docker compose exec php-fpm php phing build-demo-dev-quick
docker compose exec php-fpm php phing phpstan
docker compose exec php-fpm composer install
docker compose exec php-fpm php vendor/bin/phpunit

# Storefront commands
docker compose exec storefront pnpm run dev
docker compose exec storefront pnpm run check--fix
docker compose exec storefront pnpm install

# System commands (run directly on host)
git status
git commit -m "message"
make check-fix
ls -la packages/

# macOS only - Start/Stop with helper scripts (recommended)
./scripts/mutagen-up.sh   # Start everything (sidecar + mutagen + containers)
./scripts/mutagen-down.sh    # Stop everything

# macOS only - Manual Mutagen workflow
docker compose --profile mutagen up -d  # Start sidecar containers first
mutagen project start                    # Start file sync
mutagen sync list                        # Check sync status (wait for "Watching")
docker compose up -d                     # Start remaining containers
mutagen project terminate                # Stop file sync (before stopping containers)
docker compose --profile mutagen down    # Stop all containers
```

```bash
# Access services
# - Main app: http://127.0.0.1:8000
# - Admin: http://127.0.0.1:8000/admin
# - Storefront: http://127.0.0.1:3000
# - Adminer: http://127.0.0.1:1100
```

### Backend Development (Phing)

```bash
# Quick development builds
docker compose exec php-fpm php phing build-demo-dev-quick    # Quick dev build with demo data, skip tests
docker compose exec php-fpm php phing build-dev-quick         # Quick build preserving existing DB

# Database management
docker compose exec php-fpm php phing db-create              # Create database
docker compose exec php-fpm php phing demo-data              # Load demo data
docker compose exec php-fpm php phing db-migrations          # Run migrations
docker compose exec php-fpm php phing db-migrations-generate # Generate new migration

# Asset builds
docker compose exec php-fpm php phing npm-dev               # Development assets with source maps
docker compose exec php-fpm php phing npm-watch             # Watch for changes and rebuild
docker compose exec php-fpm php phing npm-build             # Production assets

# Code quality
docker compose exec php-fpm php phing standards-fix         # Fix all coding standards issues
docker compose exec php-fpm php phing phpstan              # Static analysis
```

### Storefront Development

```bash
# Development
docker compose exec storefront pnpm run dev                   # Start dev server (port 3000)
docker compose exec storefront pnpm run build                 # Production build

# Code quality
docker compose exec storefront pnpm run check--fix            # Fix TypeScript + ESLint + Prettier issues
docker compose exec storefront pnpm run lint--fix             # Fix ESLint + format issues
docker compose exec storefront pnpm run typecheck            # Check TypeScript

# GraphQL
docker compose exec storefront pnpm run gql                  # Generate GraphQL types from schema
```

### Testing Commands

```bash
# Backend testing (run in Docker)
docker compose exec php-fpm php phing tests                # All backend tests (unit, functional, smoke)
docker compose exec php-fpm php phing tests-unit           # Unit tests only
docker compose exec php-fpm php phing tests-functional     # Functional tests only

# Acceptance testing (run on host)
make run-acceptance-tests-base    # Run all acceptance tests
make open-acceptance-tests-base   # Open Cypress GUI for debugging

# Storefront testing (run in Docker)
docker compose exec storefront pnpm run test                  # Vitest unit tests
docker compose exec storefront pnpm run test--update          # Update test snapshots
```

### Development Workflow Commands

**Note**: Make commands run on host, but they orchestrate Docker commands internally

```bash
# Full quality check and fix workflow (run on host)
make check-fix                 # Run all checks and fixes for backend + storefront

# Individual checks (run on host)
make php-checks               # Backend standards + static analysis
make storefront-checks        # Frontend linting + formatting

# GraphQL schema sync (run on host)
make generate-schema          # Generate and sync GraphQL schema between backend and storefront
```

### Database Access Commands

**Note**: Database commands use `docker exec` directly (not compose)

```bash
# Direct PostgreSQL access (when containers are running)
docker exec shopsys-framework-postgres psql -U root -d shopsys -c "SELECT * FROM table_name;"

# Common database queries
docker exec shopsys-framework-postgres psql -U root -d shopsys -c "\dt"  # List all tables
docker exec shopsys-framework-postgres psql -U root -d shopsys -c "\d table_name"  # Describe table structure
docker exec shopsys-framework-postgres psql -U root -d shopsys -c "\l"  # List databases
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

1. **Environment Setup**: Work with Docker containers (ask user to start if not running)
2. **Backend Changes**: Edit code → `docker compose exec php-fpm php phing standards-fix phpstan` → `docker compose exec php-fpm php phing tests`
3. **Frontend Changes**: Edit code → `docker compose exec storefront pnpm run check--fix` → `docker compose exec storefront pnpm run test`
4. **GraphQL Changes**: After backend schema changes → `make generate-schema`
5. **Full Validation**: `make check-fix` before committing

**macOS Users**: Ensure `mutagen project start` is running for file synchronization

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
- Use `docker compose exec php-fpm php phing build-demo-dev-quick` for fastest development setup with demo data
- Run `make check-fix` before committing to ensure code quality standards
- Storefront development requires pnpm, backend requires PHP/Composer - both run in Docker containers
- Framework packages should only be modified via the monorepo, not directly in `/packages/`
- **Always use proper Docker commands**: `docker compose exec` for PHP/storefront commands, direct execution for git/make/system commands

## Skills / Slash Commands

When the user asks to generate upgrade notes, always use the `/generate-upgrade-notes` command instead of manually analyzing commits.

## Core Development Principles

**ALWAYS follow these principles when writing or modifying code:**

### 1. **DRY (Don't Repeat Yourself)**

- **FIRST**: Check if existing functionality can be reused
- Look for existing utilities, helpers, methods, and established patterns
- Extend or compose existing solutions rather than duplicating logic
- Extract common patterns into reusable components

### 2. **KISS (Keep It Simple, Stupid)**

- Choose the simplest solution that works
- Avoid over-engineering and complex custom solutions
- Prefer clear, straightforward approaches over clever abstractions
- Ask: "Is there a simpler way using existing code?"

### 3. **Code Reuse & Composition**

- Leverage existing tested and proven functionality
- Build complex behavior from simple, existing building blocks
- Use framework conventions and established patterns
- Prefer composition over inheritance or duplication

### 4. **Maintainability First**

- Write code that's easy to understand, modify, and debug
- Make changes in the fewest places possible
- Follow existing code conventions and patterns
- Consider long-term maintainability over short-term convenience

### 5. **Comments: Quality Over Quantity**

- **ONLY add comments when they provide actual value**
- Avoid obvious comments that just repeat what the code does
- Write self-documenting code with clear method/variable names
- Use comments to explain **WHY**, not **WHAT**
- Focus on business logic, complex algorithms, or non-obvious decisions

**Examples of GOOD comments:**

```php
// Handle edge case where role doesn't have FULL permission available
// Workaround for legacy database structure - remove after migration to v2.0
// Algorithm based on RFC 3986 specification
```

**Examples of BAD comments:**

```php
// This method returns the name
public function getName(): string

// Set the user ID
$user->setId($id);

// Loop through all items
foreach ($items as $item) {
```

### 6. **DocBlocks: Always Specify Non-Obvious Types**

- **ALWAYS add docblocks for non-obvious parameters and return types**
- Specify exact array contents, class-string types, iterators, callables, etc.
- Help IDE autocompletion and static analysis tools
- Make code intentions crystal clear for other developers

**ALWAYS specify docblocks for:**

- `array` - specify what it contains
- `class-string` - specify which class
- `callable` - specify signature
- `iterable` - specify element types
- Complex return types
- Generic types

**Examples of GOOD docblocks:**

```php
/**
 * @param class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> $context
 * @param array<string> $excludedRoles
 * @param array<int, \Shopsys\FrameworkBundle\Component\Security\Role\Role> $roles
 * @return array<string, mixed>
 */
public function buildGrid(string $context, array $excludedRoles, array $roles): array

/**
 * @param callable(string): bool $validator
 * @param iterable<\App\Entity\User> $users
 * @return \Generator<int, string>
 */
public function processUsers(callable $validator, iterable $users): \Generator
```

**Examples of BAD docblocks (missing type details):**

```php
/**
 * @param array $excludedRoles      // What's in the array?
 * @param string $context           // Any string or specific format?
 * @return array                    // Array of what?
 */
public function buildGrid(string $context, array $excludedRoles): array
```

### 7. **Before Writing New Code, Always Ask:**

- "Does this functionality already exist in the codebase?"
- "Can I achieve this by combining existing functions/methods?"
- "What's the simplest approach using what's already available?"
- "Will this be easy to maintain and understand?"
- "Does this comment add value or just repeat what the code says?"
- "Do my arrays, callables, and complex types have proper docblock specifications?"

**Example**: Instead of writing custom iteration logic, use existing "Select All" functionality and build upon it.

### 8. **Test code rules**

- To inject service in functional and GraphQl test cases do not use `$this->getContainer->get()` but use `@inject` annotation instead

### 9. **project-base folder rules**

- `project-base` folder includes a final version of files, that will be used as it is in future projects
- use `private` visibility instead of `protected`
- use typehints and return types everywhere
- use `final`

### 10. **packages folder rules**

- `packages` folder classes are expected to be extended and overridden in the `project-base` so we want to keep code as easily changeable as possible
- use `protected` visibility instead of `private`
- do not use any typehints or return types in entities and data objects (no parameter types, no return types, no property types)
- do not use `final` with one exception being FormType classes, where final is required

## Code Best Practices

### Documentation Best Practices

- **Use `{@inheritdoc}` for simple interface implementations**: When implementing an interface method and the interface docblock is sufficient, use `{@inheritdoc}`
- **Provide specific docblocks when adding value**: When you need to specify exact types, provide examples, or add implementation-specific details, write a full docblock
- **Don't duplicate generic interface documentation**: Avoid copying basic interface docs without adding specificity

**Use `{@inheritdoc}` when:**

- Interface docblock is comprehensive and sufficient
- No additional type specificity needed
- No implementation-specific behavior to document

**Use detailed docblocks when:**

- Adding specific array/generic type information
- Providing concrete examples for the implementation
- Documenting implementation-specific behavior
- Interface uses generic `mixed` types that you can specify

**Examples:**

**GOOD - Use {@inheritdoc} (simple case):**

```php
interface UserRepositoryInterface
{
    /**
     * @param int $id
     * @return \App\Entity\User|null
     */
    public function findById(int $id): ?User;
}

class UserRepository implements UserRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findById(int $id): ?User { /* implementation */ }
}
```

**GOOD - Use specific docblock (adds value):**

```php
interface DataTransformerInterface
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function transform(mixed $value);
}

class RolesGridDataTransformer implements DataTransformerInterface
{
    /**
     * Transforms role identifiers array to multidimensional form structure
     *
     * @param array<string>|mixed $value Array of role identifiers (e.g., ['ROLE_ORDER_VIEW'])
     * @return array<string, array<string, bool>> Multidimensional array [roleConstant][permission] = bool
     */
    public function transform(mixed $value): array { /* implementation */ }
}
```
