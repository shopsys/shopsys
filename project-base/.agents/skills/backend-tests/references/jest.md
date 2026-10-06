# Administration and JS tooling — Jest

Paths use the standalone project layout; apply the monorepo delta when applicable.

## Placement and existing patterns

- Application tests are colocated under `app/assets/`, discovered by the `tests:unit` script in `app/package.json` (`jest assets`). That package config selects `jsdom`.
- Translation parsing example: `app/assets/js/commands/translations/parseFile.test.js`; use its fixture layout, but assert that error paths really throw rather than using a catch-only assertion that can pass without an exception.
- Framework reference: `vendor/shopsys/framework/assets/js/admin/components/SinglePicker.test.js` tests modal opening and duplicate-handler prevention. In a downstream project vendor is read-only; in the monorepo this is editable `packages/framework/` source.
- Read the owning package configuration and per-file environment annotation. Do not assume all admin packages have a working test script or the same DOM environment.

## Test behavior

- Use Jest APIs (`jest.fn`, `jest.mock`) and the owning package's Babel/module setup; do not copy Vitest's `vi` APIs into admin tests.
- Build the minimal DOM the component needs, trigger the actual event, and assert the resulting DOM/state or meaningful external boundary call.
- For components that can be initialized repeatedly, cover duplicate listeners and cleanup when that behavior is affected.
- Reset DOM, globals, timers and handlers created by the test. Follow local setup patterns so tests do not depend on order.
- Stub external modal/translation/network boundaries where needed, but leave the behavior under test real. Prefer output assertions for parsers and transformations.

Find execution commands in `.agents/skills/shopsys-commands/SKILL.md`. Jest belongs to
backend/admin verification even though it is JavaScript; migrating it to Vitest is a
separate task, not part of writing or repairing a test.
