---
name: coding-conventions
description: Coding principles and conventions for this Shopsys project — DRY/KISS/reuse, design conventions (data model, API, simplicity), comment and docblock quality, test-code rules, the project visibility/typing rules, and documentation best practices. Read it before planning or writing PHP so your design and code match the codebase.
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

### Validation & guard clauses
- Prefer the `Webmozart\Assert\Assert` package over hand-written `if (...) { throw new ... }` guards for argument and state validation. It is already a dependency, keeps the guard on one line, and makes the intent obvious (`Assert::notSame()`, `Assert::isEmpty()`, `Assert::allInstanceOf()`, `Assert::keyExists()`, …).
- Narrow `object` or interface-typed parameters with `Assert::isInstanceOf($entity, Order::class)` instead of a `/** @var */` docblock or a manual `instanceof` check. PHPStan understands the assert (`phpstan/phpstan-webmozart-assert` is enabled in `phpstan.neon`), so the following code is analysed with the concrete type, and a wrong instance fails fast at runtime with a clear message.
- Pass the message as a closure when building it is not trivial — `Assert::isEmpty($keys, fn (): string => sprintf(...))` — so it is only built when the assertion actually fails.
- The message is passed through `sprintf()` by the library, so avoid a literal `%` in it.
- Write a dedicated exception class instead when callers need to catch that specific failure — `Assert` always throws `\InvalidArgumentException`.

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
- A comment must be **true and verified**. Never describe a hypothetical scenario you haven't confirmed ("…deleted meanwhile", "…may happen when…") — if you can't point at the code path that creates it, don't write the comment. If the code is self-describing, write none.

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

## Design conventions — apply while designing, not only in review

Decide these **before the first line is written** (when planning) and keep to them while implementing. They are the same rules the `self-review` skill checks afterwards — if you follow them from the start, the review has nothing to find.

The class and field names in the examples below (gift plans, `CartRemovedProductGift`, the cart watcher) come from one feature and only illustrate the rule — look for the equivalent in the code you are changing.

### Data model

- **Reuse an existing relation concept before inventing a bespoke one.** Look for the same shape elsewhere in the model (e.g. `OrderItem::$relatedItems` for items that belong to another item) and use it for the new case — don't add a special-purpose column (`gift_main_product_id`) next to it.
- **Never store structured data as JSON or delimited strings** (a JSON column of `"mainId:giftId"` keys) when a relation table fits. A relation gives you foreign keys, `ON DELETE CASCADE`, `orphanRemoval` and queries, and no hand-maintained array.
- **Clean up at the one place where the data changes**, not in a periodic recalculation. Forgetting a record when its owning item is removed belongs in the method that removes items (every caller goes through it); let FK cascades do the rest.
- **Scaffolding follows the nearest sibling.** An entity gets a `*Data` object and `*DataFactory` only when it is edited through forms, admin or imports. A small immutable record created from code (like `CartItem`) needs only its `*Factory` and a constructor with arguments. A value object is `readonly` with public properties, not a mutable class with getters.

### API (GraphQL Frontend API, public methods, mutation inputs)

- **Every field and argument needs a consumer.** Before adding one, find who reads it (storefront, tests, docs). Do not mirror an internal class into the schema just because the fields exist there.
- **Optional means a real caller omits it.** A nullable input nobody omits is dead flexibility every implementation must handle — make it required.
- **Names agree across layers.** The GraphQL type is named like the PHP class it exposes (`CartRemovedProductGift`), fields that sit side by side say what they are (`giftProduct` next to `mainProduct`, not `product`), and one vocabulary is used for the feature (not `CartGift` / `ProductGift` / `RemovedGift` mixed).
- **Follow sibling patterns for the same kind of class.** In result and watcher classes a value filled later is a nullable property whose getter throws `LogicException` when unset, watcher checks are named `check…Validity`, and getters/setters sit among their siblings.

### Simplicity

- **Don't guard states that can't happen.** Before adding a defensive check, prove the state is reachable — who could create it? If nobody can, leave the check out; the database constraint or the type system fails loudly if you were wrong.
- **Reuse the existing flow instead of building a parallel one.** If an existing path (e.g. the cart watcher recalculating gifts) already produces the result, change the input it reads rather than adding a second path with its own validation and recomputation.
- **Inline single-use helpers** unless the name genuinely helps the reader.
- **Compute only what is needed on hot paths** (the cart watcher and GraphQL resolvers run on every request). Iterate the small set you need (the removed gifts) instead of building the big one (every product/gift pair) and filtering it. Count the queries a new loop adds; batch or preload instead of looking items up one by one.

## Test-code rules

- In functional and GraphQL test cases, inject services with the `@inject` annotation — **not** `$this->getContainer()->get(...)`.
- Use `.agents/skills/test-writing/SKILL.md` to select the domain guide: `backend-tests` for PHP and admin Jest, `storefront-tests` for Vitest and Cypress routing.
- The test design rules (fixtures instead of magic ids, assert the scenario's preconditions, no stub-heavy duplicates of functional tests, scenario-describing names) live in `test-writing` and `backend-tests` — follow them while writing the test, not afterwards.

## Entity property hooks

Sanitization, single-property validation and null fallbacks of an entity property live in a `set` property hook, never only in a setter (a constructor, `edit()` or a subclass can bypass a setter). Rules and examples: `docs/model/entities.md#property-hooks`.

## Escaping dynamic values in administration HTML

Never put a stored value (entity name, label, e-mail, file name, domain name) into an HTML string through `t()` parameters, `sprintf()` or concatenation when the string is printed raw — flash messages, delete confirmations, Twig extension output. Pass the value to the helper that escapes it (`add*FlashTwig()` template parameters, `ConfirmDeleteResponseFactory` `$messageParameters`, `EscapedMessageInterpolator::interpolate()` parameters) or escape it yourself. Rules and examples: `docs/security/output-escaping.md`.

## Visibility & typing

Your project code is a final version used as-is, with no downstream extension surface to preserve:

- use `final` on classes;
- use `private` visibility (not `protected`);
- use typehints and return types everywhere.

The framework you extend lives read-only in `vendor/shopsys/` and follows different rules there (it's built to be extended) — but that's its code, not yours. For how to extend it correctly, see `.agents/skills/shopsys-architecture/SKILL.md`.
