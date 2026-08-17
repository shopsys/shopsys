---
name: coding-conventions
description: Coding principles and conventions for the Shopsys monorepo — DRY/KISS/reuse, comment and docblock quality, test-code rules, the per-folder visibility/typing rules (packages vs project-base vs utils), and documentation best practices. Read it before writing or modifying PHP so your code matches the codebase.
---

# coding-conventions (monorepo)

The principles are authored from the project perspective and are the same here. **Read the canonical:**

- `project-base/.agents/skills/coding-conventions/SKILL.md`

Then apply the monorepo delta (`.agents/skills/monorepo-vs-project/SKILL.md`). The canonical's "Visibility & typing" section describes only the project rule (`final` / `private` / typehints). In the monorepo that rule applies to **`project-base/` and `utils/`** (incl. `utils/releaser` — internal tooling, not released downstream), but the monorepo adds a second rule for the framework source, which here is **editable**:

- **`packages/`** — classes are expected to be extended/overridden in project-base, so keep them easy to change:
  - use `protected` (not `private`);
  - do **not** use typehints/return types in entities and data objects (no parameter, return, or property types);
  - do **not** use `final` — except FormType classes, where `final` is required.

Everything else (DRY/KISS, comments, docblocks, `{@inheritdoc}`, `@inject` test rule) is identical to the canonical.

## Dead code and the exemption config

PHPStan runs `shipmonk/dead-code-detector` from two entrypoints — the monorepo root (`phpstan.neon`) and `project-base/app/phpstan.neon`. Intentional exemptions live ONLY in the dedicated config files — root `phpstan-dead-code.neon` (for `packages/`, paths relative to the repo root) and `project-base/app/phpstan-dead-code.neon` (for project code, paths relative to `project-base/app`) — as plain PHPStan `ignoreErrors` entries. Never use `@api` annotations or inline `@phpstan-ignore shipmonk.*` comments; the config is the single place that describes what is exempt and why.

Every entry keeps the default `reportUnmatched: true`, so it must match a currently reported error: the moment an exempted member gains a real caller, PHPStan fails with an unmatched-ignore error and the entry must be deleted. The config is therefore always an exact snapshot of intentionally-kept dead code — no stale entries can accumulate.

When the detector flags a member, work down this ladder and stop at the first step that applies:

1. **Remove it** — truly dead code is deleted, together with callees that die with it.
2. **Whole-class entry** (`identifier` + `path`, no message) in the "whole api classes" section — the class belongs to an API category whose whole public surface is downstream contract: interfaces, enums, PHP attributes, traits, and framework toolkit components (Grid, the admin CRUD family, helpers, value objects, test toolkit). One entry covers every current and future dead member of that identifier kind in the class. Because unmatched entries fail the build, the entry can exist only while the class has at least one dead member — add it the first time the detector flags such a class, and delete it if the build reports it unmatched.
3. **Member entry** (`identifier` + exact-message regex + `path`) in the "individual members" section — the flagged member is intentional downstream extension surface on a class that is NOT whole-surface API (entity, facade, repository, factory, ...), or has a hidden caller no usage provider covers. When the member is kept for a hidden caller rather than being downstream api, put the reason in a `#` comment above the entry.
4. **Extend or add a usage provider** in shopsys/coding-standards (`packages/coding-standards/src/Phpstan/`) — when a whole dynamic-call channel is invisible to the detector (Twig templates, yaml config, Codeception, ...), cover the channel once instead of listing member by member. Register the provider in `packages/coding-standards/dead-code-detection.neon` — the same rule as services.yaml: consumer `phpstan.neon` files never declare `services:` themselves, they only include that file and set its parameters.

In `project-base/app/phpstan-dead-code.neon`, entries mark skeleton surface kept for generated projects.
