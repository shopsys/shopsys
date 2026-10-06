---
name: storefront-tests
description: Write, review, debug or select tests for the Shopsys Next.js storefront. Covers Vitest and React Testing Library and routes browser E2E/visual work to cypress-tests. Excludes administration Jest and PHP GraphQL server tests.
---

# Storefront tests

> **Monorepo note:** when top-level `packages/` and `project-base/` exist, read `.agents/skills/monorepo-vs-project/SKILL.md` and apply its path delta.

For implementation changes, first apply the coverage decision and test-first policy in
[test-writing](../test-writing/SKILL.md). Use the mapping below to implement that decision.

Choose the smallest layer that verifies the required behavior; a feature can need both
component-level assertions and a browser-level check without duplicating every case.

| Behavior | Layer |
|---|---|
| Pure TS/JS logic, hooks, client state, mocked API handling, SSR helpers | Vitest |
| React output, interactions, validation, accessible names and focus assertions | Vitest + React Testing Library |
| Real application navigation, checkout, browser integration | Cypress E2E |
| Actual rendering, layout, fonts and screenshot comparisons | Cypress visual regression |

For Vitest work, read [references/vitest.md](references/vitest.md).
`jsdom` component assertions do not prove real browser layout or pixel rendering.
DOM/text snapshots and PNG references are different artifacts with different update flows.

## Scenario ownership

Classify the assertion, not the component name or test directory. These are project
defaults for the configured Vitest/jsdom and Cypress E2E runners, not limitations of
what either tool can support. Do not add another runner/mode as part of ordinary test writing.

| Scenario | Primary fast coverage | Retain/add Cypress when this risk is affected |
|---|---|---|
| Form or modal | Vitest/RTL: validation combinations, pending/error states, submission payload, DOM semantics | Real entry point, submission with the API, navigation/session effects, browser focus/keyboard integration |
| Authentication | Vitest: response handling and client state transitions | Real login/logout, cookies, protected routes, reload/session persistence |
| Cart and checkout | Vitest: client transformations, debounce, controls, error handling | Product identity, quantity and displayed totals after real mutations; checkout step and order creation integration |
| Repeat order | Vitest: client handling of merge/cancel/unavailable-product responses | Real order-to-cart transfer and merge quantities, verified by explicit assertions |
| Filtering and sorting | Vitest: serialization, boundary values, state and callbacks | UI → URL → API/list results and reload/back-navigation persistence |
| Account, B2B and permissions | Vitest/RTL: visible states and client response handling | Real account mutations, identity/role/session wiring; server authorization itself belongs to backend tests |
| Deferred rendering | Vitest: scheduling, cancellation and cleanup | Actual deferred loading, interaction and layout in the browser; distinguish interaction readiness from full-page capture readiness |
| Appearance | Vitest/RTL only for meaningful DOM/accessible-state contracts | Representative desktop screenshots for layout/styles; jsdom and class-name assertions are not visual evidence |

Backend calculations and authorization enforcement belong to `backend-tests`.
Do not reproduce their full input matrix through the UI; retain storefront integration
assertions showing that the correct server result reaches the user.

## Avoid duplication without losing coverage

- Search both `storefront/vitest/` and `storefront/cypress/` before adding or moving a scenario. Follow helper calls and mocks: a rendered component or a passing request alone does not establish the claimed behavior.
- Keep boundary/error combinations in Vitest when they exercise the same client contract. Add Cypress only for a distinct integration, browser or visual risk, and reuse a relevant existing flow first.
- Tests of the same feature can be complementary. A mocked login response does not replace a real cookie/session test; a hook callback test does not replace applying the mutation and displaying its result.
- Match titles to executed actions and assertions: a reload test must reload, a multiple-filter test must apply multiple filters, and a result test must check more than the destination URL or that some element exists.
- A new Vitest case does not automatically require E2E coverage. A Cypress scenario does not automatically require a screenshot. Do not reduce tests by count or delete a browser scenario solely because a similarly named unit test exists.

## Visual coverage and reliability

- Add a screenshot only for a named visual risk/state not already represented. Prefer a stable component/viewport capture when it protects that risk; use full-page captures when page composition is the subject. Mobile captures require an explicit request.
- Assert important text, product identity, quantities, prices and success/error outcomes explicitly in the appropriate layer; screenshots supplement these assertions, not replace them.
- Keep a representative visual state instead of a screenshot for every input combination. Before removing a capture, identify the retained capture and check that it covers the same state, layout and relevant context.
- Do not remove readiness waits or scrolling needed to trigger deferred content just to shorten a test. Stabilize data/environment and wait for observable readiness; a quiet DOM alone is not evidence that delayed work finished.
- Regeneration is not approval. Keep monorepo reference generation on CI and review changes with the feature that caused them. Do not automatically raise tolerances, add retries, skip tests or overwrite baselines to hide an unexplained failure; report unresolved flakiness.

## Cypress routing

- In the monorepo, always read `.agents/skills/cypress-tests/SKILL.md`; reuse its helpers and project conventions rather than duplicating them here.
- Supplement with `cypress-docs`, `cypress-explain` or `cypress-author` only for Cypress work when those skills are installed. Their generic test-related triggers do not route Vitest, Jest or PHPUnit work to Cypress.
- In a standalone downstream project without these skills, inspect `storefront/cypress/cypress.config.ts`, `support/`, nearby specs and package scripts. Follow available project instructions and official documentation matching the installed versions; do not assume monorepo-only skills exist.
- Never start Cypress yourself; the user runs it manually. Provide a targeted spec/command and expected assertions when relevant.
- Keep generation in CI for the monorepo snapshot workflow. Regeneration is not approval: review visual changes, and do not use snapshot updates as a substitute for explaining failures.

Administration and GraphQL server contracts belong to `backend-tests`.
For exact execution commands, read `.agents/skills/shopsys-commands/SKILL.md`.
