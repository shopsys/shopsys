---
date: 2026-10-06
timezone: Europe/Lisbon
git_commit: 31b3694ad8b0fdb0f07fd615b7750555fc3e21fe
branch: tc/ssp-4128-cypress-audit
repository: shopsys/shopsys
topic: Storefront test ownership and staged audit
tags: [research, storefront, testing, vitest, cypress]
---

# Storefront test ownership audit

## Scope and status

Inventory of the working tree on 2026-10-06, after cleanup of SSP-4128 work.
HEAD alone does not describe this snapshot: test dependency upgrades and test skills
are still uncommitted. CI/filter/deferred instrumentation/modal expansion are in
stash `6579173137cff0d98200f1bd4ad3ecfac6d91176`, not active implementation.

This is a static inventory of all discovered test files and a deeper review of
representative scenarios/helpers, not a line-by-line review of every test. No Cypress,
Vitest or CI runs were performed for this audit. Counts below are source counts,
not executed cases, coverage percentages, flake rates or measured performance.
Mobile visual coverage is outside scope. Backend/admin suites are boundaries, not
targets for migration into storefront tests.

## Where the maintained rules live

- [Test decision and handoff](../../project-base/.agents/skills/test-writing/SKILL.md): changed behavior, existing coverage, reuse/extend/add/move/remove decision, verification and gaps.
- [Storefront scenario ownership](../../project-base/.agents/skills/storefront-tests/SKILL.md): which risks belong to Vitest/RTL, Cypress integration and visual coverage.
- [Vitest authoring](../../project-base/.agents/skills/storefront-tests/references/vitest.md): real subject under test, mock boundaries and assertions.
- [Cypress authoring](../../.agents/skills/cypress-tests/SKILL.md): browser interactions/readiness, explicit outcomes and manual execution.
- Root and project `AGENTS.md` route agents to these rules. Backend package namespaces/configs and the original PHP guidance remain in `backend-tests`.

This document records evidence and pending work; it is not another copy of the policy.
Skills guide agent decisions but are not a semantic CI enforcement mechanism. Existing
type checks/test jobs do not prove that the right risk was tested. Review must inspect
actions, assertions and mock boundaries, not just file names or green checks.

## Reproducible inventory

```sh
rg --files project-base/storefront/vitest | rg '\.test\.(js|ts|tsx)$' | wc -l
rg --files project-base/storefront/vitest | rg '\.test\.(js|ts|tsx)$' | awk -F/ '{print $4}' | sort | uniq -c
rg --files project-base/storefront/cypress/e2e -g '*.cy.ts' | wc -l
rg --no-heading -o '^\s*it(\.skip|\.only)?\(' project-base/storefront/cypress/e2e -g '*.cy.ts' | wc -l
rg --no-heading -o 'takeSnapshotAndCompare\(' project-base/storefront/cypress/e2e -g '*.cy.ts' | wc -l
```

Vitest discovery matches `project-base/storefront/vitest.config.js:10`.

| Vitest directory | Test files |
|---|---:|
| components | 118 |
| utils | 110 |
| gtm | 14 |
| urql | 3 |
| biome | 1 |
| cypress | 1 |
| Total | 247 |

The `vitest/cypress` file tests a console-formatting helper; it does not run Cypress.
The earlier 248-file result included the now-stashed deferred hook test and is not
a verification of the current tree.

| Cypress `e2e/` directory | Specs | Static `it` declarations | Snapshot call sites |
|---|---:|---:|---:|
| authentication | 2 | 9 | 5 |
| b2bComplaints | 1 | 5 | 3 |
| b2bUser | 3 | 14 | 7 |
| cart | 4 | 26 | 32 |
| comparison | 1 | 6 | 4 |
| customerUsers | 1 | 6 | 4 |
| deliveryOptions | 1 | 3 | 2 |
| filterAndSort | 2 | 9 | 5 |
| freeShipping | 1 | 2 | 3 |
| giftVouchers | 1 | 6 | 3 |
| giftWithProduct | 1 | 2 | 6 |
| graphql | 1 | 2 | 0 |
| limitedUser | 1 | 7 | 3 |
| matrix | 1 | 1 | 1 |
| order | 4 | 31 | 38 |
| productLists | 2 | 6 | 0 |
| seoCategory | 1 | 3 | 1 |
| ssr | 1 | 12 | 0 |
| stores | 1 | 1 | 2 |
| transportAndPayment | 3 | 18 | 22 |
| visits | 4 | 22 | 5 |
| watchdog | 1 | 1 | 0 |
| Total | 38 | 192 | 146 |

Separate from E2E: `cypress/smokeTests/smokeTests.cy.ts:261` generates route/page
cases dynamically. Its runtime count cannot be inferred from these `it` counts.
The screenshot count is not a PNG inventory: it excludes runtime multiplicity and
does not establish whether references are current.

## Cross-layer evidence and proposed decisions

Paths in this section are relative to `project-base/storefront/`. Line numbers refer
to the audited working tree. Decisions below are a backlog, not implemented repairs.

| Scenario | Current fast coverage | Current Cypress protection / gap | Proposed decision |
|---|---|---|---|
| Login | `vitest/utils/auth/useLogin.test.tsx:100` checks mutation consequences with mocked mutation/store/navigation. | `cypress/e2e/authentication/login.cy.ts:22` checks refresh-cookie protection; `:40` login/logout/refresh and persisted state. | Keep complementary layers; a hook callback does not replace a real session. |
| Login popup | `vitest/components/Blocks/Popup/LoginPopup.test.tsx:72` checks dialog semantics/focus, while form, focus trap and keypress hook are mocked. | Existing login/cart flows exercise real entry points; the unit test does not prove full focus containment or login submission. | Keep DOM semantics in RTL; identify any browser-specific gap before adding another flow/capture. |
| Cart quantity | `vitest/components/Spinbox/SpinboxDebounce.test.tsx:30` covers timed batching; `vitest/utils/cart/useAddToCart.test.ts:99` covers mocked mutation/GTM direction. | `cypress/e2e/cart/cartPage.cy.ts:83` checks real request quantities and dataLayer events. Fast/slow cases at `:49` and `:118` capture matching visual endpoints. | Keep real mutation/analytics connection; assess consolidation of repeated captures and timing permutations after explicit result assertions. |
| Repeat order | No repeat-operation assertion found in current Vitest content search; order presentation/validation tests do exist. | Six flows in `cypress/e2e/order/orderRepeat.cy.ts:21` onward assert cart URL + screenshot, not returned product identity/quantities. Helpers in `orderSupport.ts:327` and `:337` perform actions. | First pilot: explicit cart results. Do not replace real transfer/merge integration with a mocked hook or drop the six cases blindly. |
| Checkout fields | `vitest/components/Pages/Order/ContactInformationFormContent.test.tsx:45` tests delivery-section presence, with child blocks/form hooks mocked. | `cypress/e2e/order/contactInformation.cy.ts:120` really reloads; logged-in case `:127` promises refresh but never reloads before capture. | Correct the intended browser scenario and assert retained field values; do not call component visibility coverage persistence coverage. |
| Order creation | Client validation/field tests are narrower than server order creation. | `cypress/e2e/order/createOrder.cy.ts:45` and `:82` use identical steps and the same registered email despite different labels. | Clarify intended inputs; either distinguish the scenarios or consolidate genuine duplication. |
| Price/parameter filters | `vitest/components/Blocks/Product/Filter/FilterGroupPrice.test.tsx:179` checks callback; `vitest/utils/queryParams/useUpdateFilter.updateFilterPrices.test.ts:82` checks query mapping. | `categoryDetailFilterAndSort.cy.ts:22` checks loose URL substrings and input persistence, not matching products. `:112` conditionally weakens checks when input is missing. `parameterFilter.cy.ts:60` says multiple parameters but selects one. | Keep real UI → URL → results/reload connection, strengthen expected results, keep input/serialization permutations in Vitest. |
| Grid/list mode | `vitest/components/Blocks/Product/ProductsList/ProductListViewModeToggle.test.tsx:46` checks state/interaction against mocked cookie store. | `cypress/e2e/filterAndSort/categoryDetailFilterAndSort.cy.ts:174` checks real listing, cookie, capture and reload. | Complementary; accessible state is not proof of persisted browser layout. |
| Comparison remove/undo | `vitest/utils/productLists/useComparison.test.tsx:69` and `:92` exercise error/notification/focus behavior with mocked underlying product-list hook. | `cypress/e2e/comparison/productComparison.cy.ts:113` reorders/removes/undoes/reloads and checks product order. | Keep the assembled persistence flow; keep callback/error permutations low. |
| Account/B2B/dialogs | `OrderItemProducts.test.tsx:27` covers image mapping/truncation, not actual layout. | `cypress/e2e/customerUsers/customerUsers.cy.ts:55` exercises dialog/API save; `:181` and `:220` exercise roles. `deliveryOptions/deliveryOptionsPopup.cy.ts:33` combines assertions and a targeted popup capture. | Prefer representative integrated/visual states; server authorization belongs to backend tests. A hidden button is not proof of enforcement. |
| Deferred content | `vitest/components/Layout/Header/Navigation/DeferredNavigation.test.tsx:22` mocks the deferred hook; tests check wrapper/lazy fallback. | Capture helper scrolls; real `utils/useDeferredRender.ts:38` schedules timers/transitions. | Wrapper test is not scheduler coverage. Assess a focused scheduler test separately; preserve actual browser readiness. |
| SSR and smoke | Existing SSR helpers/hydration unit tests test narrower boundaries. | `cypress/e2e/ssr/serverSideRendering.cy.ts:63` checks actual cookie/SSR/hydration; other cases inspect HTTP HTML. Smoke route/UUID branches have different error collection. | Do not move production SSR/HTTP contracts into jsdom without an equivalent integration harness. Audit smoke branch consistency separately. |

Additional consolidation candidate: `cypress/e2e/matrix/matrixTest.cy.ts:19` and
`cypress/e2e/visits/simpleVisitsWithScreenshots.cy.ts:24` capture the same homepage
setup. However, `cypress/cypress.config.ts:164` uses matrix as group fallback;
deleting the spec before checking routing would be unsafe.

## Reliability and CI boundaries

- `cypress/support/index.ts:92` waits for stable DOM, dispatches resize, checks loaders, hydration and stability again. Visit/reload helpers call it twice. Repetition is a review candidate, not proof it can be removed safely.
- `support/index.ts:298` checks font status before capture scrolling; `:328` scrolls bottom/top with time-based waits. `utils/useDeferredRender.ts:65` defines delayed waves reaching 900/1000 ms. A quiet DOM can precede later work. No pending-deferred marker is active.
- Blackouts skip missing elements (`support/index.ts:418`); required content needs an assertion. A blackout must not hide the behavior the scenario claims to check.
- A `cy.intercept` call is not necessarily a stub: login/cart helpers often observe the real response, while some delivery data is normalized. Classify the specific response handling, not the presence of the API name.
- `cypress/cypress.config.ts:44` still allows `visualRegressionErrorThreshold: 0.005`. This audit does not calibrate a replacement or attribute any 1px difference to a proven cause.
- `.github/workflows/docker-build.yaml:415` runs Vitest separately; `:612` defines Cypress groups. These are execution buckets, not semantic test ownership. The `others` bucket and fallback require checking before moving/deleting specs.
- Regeneration copying/committing candidates at `.github/workflows/docker-build.yaml:811` is not visual approval. The stashed CI/filter work must be re-evaluated, not assumed complete or automatically restored.

## Rules tightened in this audit

The maintained skills now require a named behavior, existing scenario and a justified
layer decision, plus verification/gaps in the implementation handoff. Moving live
coverage requires a verified replacement for each retained risk; obsolete behavior may
lose its obsolete test with an explanation. No per-file test quota or duplicated
coverage percentage is introduced.

The Cypress skill now routes through this policy even when invoked directly. Its
filter/sort examples no longer prescribe arbitrary 1500 ms sleeps, retries are not
presented as a flakiness repair, and default manual commands use regression rather
than reference-generation mode. The skill update path points to `.agents`, not
`.claude`. Test implementation and CI behavior were not changed in this batch.

## Ordered follow-up and acceptance evidence

| Stage | Work | Evidence required before advancing |
|---|---|---|
| 1 — ownership and inventory | This audit and canonical skill rules | Static inventory, scenario examples, links/commands checked; policy applied to representative development requests. Done for this batch, not an exhaustive assertion review. |
| 2 — repeat-order pilot | Trace expected products/quantities for list/detail, empty/merge/overlap cases; add direct outcome assertions, choose visual states separately | Targeted lower-layer checks where relevant; user-run Cypress regression showing cart results. No blanket screenshot regeneration. |
| 3 — cart/filter/checkout | Fix title/action/assertion mismatches; assess timing permutations and repeated visual endpoints | Each removed case/capture mapped to a retained risk and verified replacement; preserve real API/session/analytics checks. |
| 4 — capture reliability and review | Separate interaction/full-capture readiness; validate fonts/data/blackout wrappers; design label → candidates → review → regression workflow | Repeated same-commit CI captures plus intentional small icon/text/shift/dimension changes. Review actual comparisons, not historical viewer metrics. |
| 5 — CI speed | Measure build/install/data prep/spec/artifact time and imbalance before choosing cache/sharding changes | Comparable before/after timings and unchanged meaningful coverage. Record retries separately; no promised speedup without measurement. |

Open decisions: exact visual states to retain, backend coverage of repeat-order merge,
intended registered/unregistered order inputs, current flake frequency and reproduction,
and which candidate-review mechanism fits the existing GitHub process. No blanket
Vitest migration, modal screenshot expansion, retry/tolerance change or stash restore
is approved by this document.

## Documentation basis

Read on 2026-10-06. The layer ownership above is a Shopsys policy, not a claim that
either framework mandates this particular split.

- [Cypress testing types](https://docs.cypress.io/app/core-concepts/testing-types): isolated component checks and full application flows protect different boundaries.
- [Cypress best practices](https://docs.cypress.io/app/core-concepts/best-practices): independent scenarios, controlled setup, stable selectors and meaningful waits. The LLM markdown endpoints were attempted; HTML was used after retrieval errors.
- [Cypress visual testing](https://docs.cypress.io/app/tooling/visual-testing): visual comparisons supplement functional assertions and depend on controlled capture state.
- [Vitest environments](https://vitest.dev/guide/environment.html): the configured jsdom environment emulates browser APIs; Browser Mode is a separate configuration, not enabled by the name Vitest alone.
- [Testing Library principles](https://testing-library.com/docs/guiding-principles/): test observable DOM/user behavior rather than component internals.
