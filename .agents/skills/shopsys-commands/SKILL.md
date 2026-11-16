---
name: shopsys-commands
description: The command catalog for the Shopsys monorepo — how to build, run, test, check code, access the database, and sync the GraphQL schema, including the macOS Mutagen workflow and per-package test configs. Read it when you need the exact command for a task and which run inside Docker containers versus on the host.
---

# shopsys-commands (monorepo)

The command catalog is authored from the project perspective and is almost identical here. **Read the canonical:**

- `project-base/.agents/skills/shopsys-commands/SKILL.md`

Then apply the monorepo delta (`.agents/skills/monorepo-vs-project/SKILL.md`). For commands, the only monorepo additions are:

- **Package unit tests use that package's own config and an explicit test path:** inside `php-fpm`, use `vendor/bin/phpunit --configuration packages/<package-name>/phpunit.xml packages/<package-name>/tests/<path> --filter <name>`. Some configs do not declare suites, so config plus filter alone is insufficient. `utils/releaser` follows the same pattern with its own config and tests. Project suites use `--configuration project-base/app/phpunit.xml`.
- **Administration Jest:** `docker compose exec php-fpm php phing tests-unit-jest` runs framework and application JS tests. For one framework test use `docker compose exec php-fpm npm --prefix packages/framework/assets run tests:unit -- --runTestsByPath js/admin/components/SinglePicker.test.js`. For application JS, add `--prefix project-base/app` after `npm` in the canonical command. Do not use the placeholder test script in `packages/administration/assets`.
- **Coding standards:** run `docker compose exec php-fpm php phing standards-fix` (applies the repository skip rules, runs `entities-dump` first; `ecs-diff` is not a diff-only check — it runs ECS over all paths). Do not use `vendor/bin/ecs check <file>` for a verdict, and do not run ECS on the whole of `packages/framework/src` with `--fix` — it rewrites old migrations. PHPStan: `vendor/bin/phpstan analyse -c phpstan.neon --level=5 --memory-limit=2G /var/www/html/<changed files>` as the default user (never `-u root`).
- **`packages/` is editable source** — the same Docker/Phing/pnpm/Make commands apply; only the code you're building lives in `packages/` rather than `app/` (path remap per the delta). The Mutagen workflow, service URLs, and DB access are identical.
