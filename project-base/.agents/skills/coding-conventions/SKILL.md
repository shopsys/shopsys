---
name: coding-conventions
description: Coding principles and conventions for this Shopsys project — DRY/KISS/reuse, comment and docblock quality, test-code rules, the project visibility/typing rules, and documentation best practices. Read it before writing or modifying PHP so your code matches the codebase.
---

> **Monorepo note:** if the repository has top-level `packages/` and `project-base/` directories (the shopsys/shopsys monorepo layout, not a standalone project), also read `.agents/skills/monorepo-vs-project/SKILL.md` and apply its delta (package-first + path/role remap) on top of this skill.

# Shopsys coding conventions

Follow these whenever you write or modify code in this project.

## Core principles

### DRY (Don't Repeat Yourself)
- **First**, check whether existing functionality can be reused — utilities, helpers, methods, established patterns (in `app/src/` and in `vendor/shopsys/`).
- Extend or compose existing solutions rather than duplicating logic; extract common patterns into reusable components.

### KISS (Keep It Simple)
- Choose the simplest solution that works; avoid over-engineering and clever abstractions.
- Ask: "Is there a simpler way using existing code?"

### Reuse & composition
- Leverage tested, proven functionality; build complex behavior from simple existing blocks.
- Use framework conventions; prefer composition over inheritance or duplication.

### Maintainability first
- Write code that's easy to understand, modify, and debug; change things in the fewest places.
- Follow existing conventions; favor long-term maintainability over short-term convenience.

### Before writing new code, ask:
- Does this already exist in the codebase (`app/src/`, `vendor/shopsys/`)? Can I combine existing functions/methods?
- What's the simplest approach using what's available? Will it be easy to maintain?
- Does each comment add value? Do my arrays/callables/complex types have proper docblocks?

## Comments — quality over quantity

- **Only** add comments that provide real value. Write self-documenting code with clear names.
- Explain **why**, not **what**; focus on business logic, complex algorithms, non-obvious decisions.

```php
// GOOD
// Handle edge case where role doesn't have FULL permission available
// Workaround for legacy database structure - remove after migration to v2.0
// Algorithm based on RFC 3986 specification

// BAD
// This method returns the name
public function getName(): string
// Set the user ID
$user->setId($id);
```

## DocBlocks — always specify non-obvious types

Add docblocks for non-obvious parameters and return types (helps IDEs and static analysis). Always specify: `array` contents, `class-string` targets, `callable` signatures, `iterable` element types, complex/generic return types.

```php
// GOOD
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

// BAD — what's in the array? which class? array of what?
/**
 * @param array $excludedRoles
 * @param string $context
 * @return array
 */
public function buildGrid(string $context, array $excludedRoles): array
```

### `{@inheritdoc}` vs a detailed docblock

- Use `{@inheritdoc}` when the interface docblock is comprehensive, no extra type specificity is needed, and there's no implementation-specific behavior to document.
- Write a detailed docblock when you can add specific array/generic types, concrete examples, or document implementation-specific behavior (e.g. specifying a `mixed` the interface leaves generic).

```php
// Simple case → {@inheritdoc}
/**
 * {@inheritdoc}
 */
public function findById(int $id): ?User { /* ... */ }

// Adds value → specific docblock
/**
 * Transforms role identifiers array to multidimensional form structure
 *
 * @param array<string>|mixed $value Array of role identifiers (e.g., ['ROLE_ORDER_VIEW'])
 * @return array<string, array<string, bool>> [roleConstant][permission] = bool
 */
public function transform(mixed $value): array { /* ... */ }
```

## Test-code rules

- In functional and GraphQL test cases, inject services with the `@inject` annotation — **not** `$this->getContainer()->get(...)`.
- See `.agents/skills/test-writing/SKILL.md` for the full testing guide.

## Entity property hooks

Sanitization, single-property validation and null fallbacks of an entity property live in a `set` property hook, never only in a setter (a constructor, `edit()` or a subclass can bypass a setter). Rules and examples: `docs/model/entities.md#property-hooks`.

## Visibility & typing

Your project code is a final version used as-is, with no downstream extension surface to preserve:

- use `final` on classes;
- use `private` visibility (not `protected`);
- use typehints and return types everywhere.

The framework you extend lives read-only in `vendor/shopsys/` and follows different rules there (it's built to be extended) — but that's its code, not yours. For how to extend it correctly, see `.agents/skills/shopsys-architecture/SKILL.md`.
