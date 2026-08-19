---
name: test-writing
description: >
  This skill MUST be used when the user asks to write tests, add tests, create test cases,
  run/execute/re-run/debug tests, test a specific class or method, or when working on any
  *Test.php file in this Shopsys application. Applies codebase-specific best practices across
  unit, functional, GraphQL API, and smoke test layers.
version: 1.0.0
---

# test-writing (monorepo)

The canonical instructions for this skill are authored from the project perspective. **Read them first:**

- `project-base/.agents/skills/test-writing/SKILL.md`

Then apply the monorepo delta (package-first + path/role remap):

- `.agents/skills/monorepo-vs-project/SKILL.md`

## What the monorepo adds back

The canonical describes only the project test layers. In this monorepo, packages have their own unit tests, and paths are remapped:

- **Package unit-test layers** — beyond the project's `Tests\App\Unit\…`, the monorepo adds a unit layer per package, mirroring the source location:
  - `packages/framework/` → `Tests\FrameworkBundle\Unit\…`
  - `packages/frontend-api/` → `Tests\FrontendApiBundle\Unit\…`
  - `packages/<foo>/` → `Tests\<Foo>Bundle\Unit\…`
- **Each package unit test runs with that package's own config** — use `--configuration packages/<pkg>/phpunit.xml` (no `--testsuite` needed), instead of the canonical's single `app/phpunit.xml`.
- **Path remap** — every `app/…` / `storefront/…` path in the canonical is under `project-base/app/…` / `project-base/storefront/…` here, per the delta.

Everything else — layer selection for project tests, behavioral-testing rules, AAA, naming, `@inject`, fixtures, GraphQL helpers, skeletons, code style, and the phing targets — follows the canonical unchanged.
