# Storefront — Vitest and React Testing Library

Paths use the standalone project layout; apply the monorepo delta when applicable.

## Configuration and placement

- Read `storefront/vitest.config.js` and `storefront/vitest/setup.ts`. Tests are discovered under `vitest/**/*.test.{js,ts,tsx}`, not beside production files outside that directory.
- The current environment is `jsdom`, with React and tsconfig-path plugins. `clearMocks` and `restoreMocks` are enabled. Do not add duplicate global setup to individual files.
- Shared setup loads `@testing-library/jest-dom/vitest` matchers and initializes `window.__ENV`. This dependency does not mean the runner is Jest: use Vitest's `vi` APIs.
- Vitest 5 runs with Vite 8 and jsdom 30. The storefront Docker images use Node 24.15.0, the minimum supported Node 24 release for jsdom 30; rebuild an older image before running these tests. Keep the Undici override limited to versions below 8 so jsdom can use its required Undici 8 dependency.
- Reuse `storefront/vitest/helpers/mockPublicConfig.ts` for public/domain config fixtures. Copy before mutating shared values; choose domain, locale and timezone explicitly when the behavior depends on them.

## Select a nearby pattern

- Pure logic: `storefront/vitest/utils/serialization/serializeJsonForScriptTag.test.ts`.
- Hooks: `storefront/vitest/utils/cart/useAddToCart.test.ts` (`renderHook`).
- Components and accessibility: `storefront/vitest/components/Blocks/Popup/LoginPopup.test.tsx` (`render`, role queries, focus/description assertions).
- Text/DOM snapshots: `storefront/vitest/components/ExtendedNextLink/ExtendedNextLink.test.tsx` and its `.snap` files.
- SSR/hydration: `storefront/vitest/components/ExtendedNextLink/ExtendedNextLink.hydration.test.tsx`.

Reuse the relevant setup and providers; do not copy unrelated mocks from a large test.

## Assertions and isolation

- Assert observable results: rendered text, accessible state, navigation parameters, returned data or a meaningful external effect. Do not mock the behavior being tested.
- Prefer accessible role/name queries for user-facing controls. Use existing helpers and test IDs where semantic queries do not identify the target reliably.
- Await asynchronous interactions and observable updates; use existing `findBy`/`waitFor` patterns rather than arbitrary delays. Keep actions outside retrying assertion callbacks.
- Fix time and inputs for time-sensitive behavior. When using fake timers, globals, browser API stubs, listeners or state stores, restore/reset what the test changes; mock restoration alone is not full application-state cleanup.
- Mock Next.js routing, GraphQL and browser APIs at the relevant boundary, following nearby tests. A mocked API response verifies client handling, not the backend contract.
- Import the production function/component whose behavior is under test. Do not recreate its algorithm or markup in the test and then assert that local copy. A test wrapper may supply providers, but must exercise the real subject.
- A callback/payload assertion is useful when that is the component's contract. State that boundary; do not claim it proves persistence, a real API result or browser navigation. If a child or hook is mocked, its behavior is outside that test's coverage.
- Test debounce/timing contracts with controlled timers and restore them afterward; do not repeat the whole UI flow for every delay value. Keep browser cases for distinct real integration risks.
- Review `.snap` diffs as code. Prefer explicit assertions for important values; do not bulk-update snapshots to hide unexplained failures. Always await asynchronous assertions such as `toMatchFileSnapshot`; Vitest 5 rejects unawaited assertions. Vitest snapshot updates never regenerate Cypress PNGs.
- Accessibility assertions cover the tested semantics/interaction, not a complete accessibility audit. `jsdom` does not verify actual layout, fonts or image pixels.

Use the `shopsys-commands` catalog for single-run, targeted, watch and snapshot-update
commands. The default test script is interactive/watch-oriented; choose a bounded run
for targeted automated verification. Do not run Cypress as a follow-up to a Vitest task
without respecting the project's manual-execution rule.
