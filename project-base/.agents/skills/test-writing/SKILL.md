---
name: test-writing
description: Decide whether to add, extend or reuse tests for Shopsys implementation changes, then select the testing layer and domain specialist. Also use for testing requests with unclear or cross-domain scope.
---

# Test coverage decisions and routing

> **Monorepo note:** when top-level `packages/` and `project-base/` exist, read `.agents/skills/monorepo-vs-project/SKILL.md` and apply its path and ownership delta.

## Decide before implementing

For each implementation change, identify the intended behavior and search existing tests
and helpers before adding coverage. Briefly state which scenario needs a new or extended
test, which existing tests suffice, or why no new behavioral test is justified. This is
part of implementation, not a separate approval gate. Read-only requests do not authorize
test or application edits.

Make the decision concrete: name the changed behavior, the existing test file/scenario,
and whether to **reuse, extend, add, move or remove** coverage. For changes spanning
multiple layers, state the distinct risk each layer protects. A shared feature name
or line-coverage percentage is not evidence of duplication.

| Change | Default decision |
|---|---|
| Reproducible bug | Add or extend a regression test; where practical and execution is permitted, demonstrate failure on the original bug before fixing it. |
| New or changed behavior | Cover the changed contract, relevant boundaries and failure paths in the smallest layer that can verify them; extend existing scenarios instead of duplicating them. |
| Integration between UI, API, session or navigation | Check whether an existing integration/E2E scenario protects the connection; add or extend one when the changed risk is not covered. Mocked responses alone do not verify the real connection. |
| Visual-only change | Review the relevant visual baseline and add a representative capture only if coverage is missing. Do not add a unit test merely to duplicate CSS values. |
| Refactor without a behavior change | Reuse existing behavioral tests; add coverage only for a relevant gap. Do not rewrite expectations just to match new internals. |
| Documentation, comments or mechanical changes with no behavior change | Usually no new behavioral test; use relevant documentation, type or static checks and explain the choice. |
| Test or build infrastructure | Verify the changed tool directly and the affected tests; broaden verification when shared configuration or setup changes. |

## Test-first, not tests for their own sake

- Prefer test-first for reproducible bugs and well-defined logic: derive the expected result from the requirement or a concrete example, observe a meaningful failure, implement the change, then refactor with the test passing. An import/setup error is not proof of reproducing the bug.
- When a red run is impractical or prohibited, explain the limitation and provide the reproduction scenario. Never claim red/green evidence from an unexecuted test or overwrite unrelated work to recreate it.
- Assert observable behavior or boundary contracts. Do not compute the expected result with the same implementation being tested or mock away the behavior the test claims to cover.
- Do not add a test per changed file or chase test counts/coverage percentages. Keep tests at multiple layers only when they protect distinct risks.
- If a test fails, determine whether the implementation, expectation or requirement is wrong before editing it. Do not weaken assertions or regenerate snapshots merely to obtain a pass.

## Verification and handoff

Run the relevant tests only within the task's execution permissions; start with affected
scenarios and broaden based on shared dependencies and risk. This policy does not trigger
whole-task preflight or an unrelated audit. Report what ran and its result separately from
mock-only coverage and unverified integration/browser behavior. For manual Cypress runs,
provide the relevant spec/command and expected outcome; do not claim it passed.

Before handing off an implementation, check the changed tests against their stated
behavior: do the actions actually exercise it, and would a wrong result fail an assertion?
Report the chosen coverage, actual verification and any remaining gap in the handoff,
not as mandatory boilerplate in every test or PR description. If moving/removing a test,
identify its replacement and any browser/API/visual risk the replacement cannot verify;
retain that coverage until the replacement is verified within the allowed workflow.
For intentionally removed behavior, explain why its test is obsolete and no replacement
or remaining risk needs coverage; do not preserve dead tests just to satisfy this rule.

Current specialist skills define policy; nearby tests are examples, not exceptions to it.
When changing testing infrastructure, update the owning skill and its affected examples
in the same change. Keep shared rules in the canonical project skill and monorepo-only
paths/package rules in its root wrapper; do not maintain parallel copies of the policy.

## Select the domain and layer

Choose by product domain, not by language or the CI job name. Administration belongs
to backend testing even when its code is JavaScript. State the chosen layer and why.

| Change or request | Read |
|---|---|
| PHP logic, Symfony services, GraphQL API, backend smoke/performance, administration or its JS tooling | [backend-tests](../backend-tests/SKILL.md) |
| Next.js storefront utilities, hooks, React components, SSR, user flows or visual regressions | [storefront-tests](../storefront-tests/SKILL.md) |
| Known Cypress task | Project `cypress-tests` skill when available; storefront-tests describes the downstream fallback |
| CI/build helpers, shell scripts or image-comparison tooling | Inspect the owning script, existing tests and runner; reuse that runner rather than selecting Cypress merely because a file is in its directory |

For a cross-domain change, use both domain skills only for the affected layers.
GraphQL server contracts belong to backend; storefront handling of API responses belongs
to storefront. A shell/Node tool test is not a new product domain requiring another skill.

## Boundaries

- Read `.agents/skills/shopsys-commands/SKILL.md` when selecting execution commands. Use targeted verification within the user's requested scope; never start or stop containers yourself.
- Never start Cypress yourself; the user runs it manually. Provide the relevant spec/command and expected result when useful.
- Do not update snapshots merely to make a failure pass. Establish that the changed behavior is intended and review the resulting diff.
- Lint, type checking, static analysis, generated-artifact checks and builds are separate quality gates, not additional behavioral test suites.
- For requested agent-driven acceptance checking on a deployed environment, use `acceptance-criteria-check` if available. It is not a replacement for the repository's automated tests.
