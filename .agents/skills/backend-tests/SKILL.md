---
name: backend-tests
description: Write, review, debug or select tests for Shopsys backend and administration, including PHPUnit PHP/GraphQL tests and Jest admin JavaScript tests. Use for backend Test.php files and administration JS tests, not Next.js storefront tests.
---

# Backend and administration tests (monorepo)

Read `project-base/.agents/skills/backend-tests/SKILL.md` and the relevant reference,
then apply `.agents/skills/monorepo-vs-project/SKILL.md`.

## What the monorepo adds back

The canonical describes the project test layers. In this monorepo, packages also
have their own unit tests, and paths are remapped:

- **Package unit-test layers** — beyond the project's `Tests\App\Unit\…`, the monorepo adds a unit layer per package, mirroring the source location:
  - `packages/framework/` → `Tests\FrameworkBundle\Unit\…`
  - `packages/frontend-api/` → `Tests\FrontendApiBundle\Unit\…`
  - `packages/<foo>/` → `Tests\<Foo>Bundle\Unit\…` (follow the actual package namespace and layout).
- **Each package unit test runs with that package's own config** — use `--configuration packages/<pkg>/phpunit.xml` and an explicit test file/directory, not the application's config. No `--testsuite` is needed when selecting tests by path. Some package configs have no `<testsuites>`, so config plus `--filter` alone is insufficient. The root `build.xml` records the test directories used by Phing.
- **Path remap** — repository-relative `app/…` and `storefront/…` paths in the canonical become `project-base/app/…` and `project-base/storefront/…`, per the delta. Application suites use `project-base/app/phpunit.xml`. For container commands, use the command catalog's explicit Docker paths rather than mechanically remapping the standalone container working directory.
- **Utility tests** — `utils/releaser/tests/` also uses its own `utils/releaser/phpunit.xml` and an explicit test path.

All shared PHP guidance — layer selection for project tests, behavioral-testing rules,
AAA, naming, `@inject`, fixtures, GraphQL helpers, skeletons and code style — remains
defined by the canonical [PHPUnit reference](../../../project-base/.agents/skills/backend-tests/references/phpunit.md).
Apply it with the monorepo delta; this domain split does not replace those rules.
Phing targets and execution commands remain in `.agents/skills/shopsys-commands/SKILL.md`.

## Administration and suite composition

- Framework/admin JS tests live alongside code in `packages/framework/assets/js/` and run through that package's Jest script. Application JS tests live in `project-base/app/assets/` and use its script. `packages/administration/assets/package.json` currently has a placeholder test script, not a working test suite; inspect the owner before choosing a command.
- `phing tests` includes PHPUnit unit/functional/smoke and the Jest unit targets. It does not include the dedicated performance target.

Use `.agents/skills/shopsys-commands/SKILL.md` for commands and Docker context.
