---
name: backend-tests
description: Write, review, debug or select tests for Shopsys backend and administration, including PHPUnit PHP/GraphQL tests and Jest admin JavaScript tests. Use for backend Test.php files and administration JS tests, not Next.js storefront tests.
---

# Backend and administration tests

> **Monorepo note:** when top-level `packages/` and `project-base/` exist, read `.agents/skills/monorepo-vs-project/SKILL.md` and apply its delta. The root backend-tests entrypoint adds package test guidance.

Administration belongs to this domain, including its JavaScript and translation tools.
Select the relevant reference before changing or diagnosing tests:

- **PHP / PHPUnit** → [references/phpunit.md](references/phpunit.md): unit, Symfony/DB integration, GraphQL B2C/B2B, smoke and performance.
- **Administration / Jest** → [references/jest.md](references/jest.md): DOM interactions and JS tooling. Do not apply PHPUnit conventions to these files.

Reuse nearby tests, fixtures and helpers. Describe the selected layer and the observable
behavior being verified. A change crossing PHP and admin JS may need both references;
a pure PHP change does not.

Exact commands and suite/config selection live in `.agents/skills/shopsys-commands/SKILL.md`.
Run only verification appropriate to the requested scope; broad suites and performance
data preparation are not automatic follow-up steps. Do not start/stop containers.

Next.js storefront tests belong to `storefront-tests`, even when they call GraphQL.
Conversely, `FrontendApiFunctional` is a backend PHPUnit suite, not a storefront suite.
