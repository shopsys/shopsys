---
name: storefront-pr-review
description: >
  Reviews storefront pull/merge requests with risk-based static analysis,
  Jira context, project conventions, call-site tracing, test coverage audit, and
  Playwright runtime verification when available. Use when the user asks for a
  frontend/storefront/Next.js PR/MR code review, especially with a GitHub PR or
  GitLab merge request URL, Jira issue URL/key, or review environment URL.
user_invocable: true
version: 1.0.0
---

> **Monorepo note:** if the repository has top-level `packages/` and `project-base/` directories (the shopsys/shopsys monorepo layout, not a standalone project), also read `.agents/skills/monorepo-vs-project/SKILL.md` and apply its delta (package-first + path/role remap) on top of this skill.

# Storefront PR Review

## Usage

```bash
/storefront-pr-review <PR_OR_MR_URL> [JIRA_ISSUE_URL_OR_KEY]
```

`PR_OR_MR_URL` is required — a **GitHub pull request** or a **GitLab merge request** URL. Jira context is optional, but when a Jira URL or issue key is provided or discoverable from the PR/MR, fetch it first and summarize the task scope before reviewing.

Do not create branches, switch branches, commit, push, or post PR/MR or Jira comments unless explicitly asked.

## Fetching the PR/MR

Detect the host from the URL and use the matching CLI:

- **GitHub** → the `gh` CLI: `gh pr view <url> --json ...`, `gh pr diff <url>`, `gh api ...`.
- **GitLab** → the `glab` CLI (installed): `glab mr view <url>`, `glab mr diff <url>`, `glab api ...`. GitLab requires authentication — private merge requests are unreachable without it. If a `glab` call fails with an auth error, check `glab auth status` and ask the user to run `glab auth login` for the relevant host; don't fall back to unauthenticated fetches.

## Role

You are a senior storefront reviewer. Review the PR/MR for real bugs, regressions, missing tests, maintainability risks, and accessibility problems. Findings must be evidence-based and tied to exact files, lines, runtime behavior, or requirements.

Avoid broad praise and generic advice. Prefer fewer, higher-confidence findings over speculative noise.

## Data Sources

Use concrete data only:

- project instructions: `AGENTS.md`
- relevant docs in `docs/`
- PR/MR metadata, description, labels, target branch, changed files, diff, and reviews
- Jira issue description and comments when available
- changed files plus surrounding implementation context
- existing storefront patterns in `storefront/`
- Playwright/browser verification when a review URL or local environment is available

If a source is unavailable, continue where possible and report the gap under `Not verified`.

## Stop Conditions

Stop only when the PR/MR metadata and diff cannot be fetched.

Do not hard-stop only because Jira, Atlassian MCP, Playwright, review URL, or review environment is unavailable. Continue with static review and clearly mark missing verification.

## Required Method

### 1. Scope And Risk Map

Before detailed review:

- Fetch PR/MR title, description, target branch, changed files, diff stat, labels, and review URL if present.
- Fetch Jira issue and comments when a Jira URL/key is provided or discoverable.
- Identify generated files and do not spend review effort on them unless source generation is relevant.
- Build a risk map from changed files.

Treat these areas as high risk:

- checkout, cart, login, registration, customer area
- pricing, availability, transport, payment, order creation
- forms, validation, error/loading states
- dialogs, popups, drawers, overlays, modals
- accessibility attributes, focus handling, keyboard behavior, live regions, sr-only text
- GraphQL operations, generated types, cache updates, SSR/CSR boundaries
- shared components, hooks, utilities, state stores, routing, URL state

### 2. Static Review

Read changed source files in full enough to understand behavior, not only diff hunks.

For every changed shared component, hook, utility, prop contract, GraphQL operation, or accessibility attribute:

- trace call sites with `rg`
- inspect representative callers, especially checkout/cart/login/product flows
- verify default values and fallback logic
- check edge values: `null`, `undefined`, `''`, `0`, empty arrays, missing translations, loading state, error state, unauthenticated/authenticated state
- check stale closures, unstable dependencies, unnecessary rerenders, and async timing issues
- verify TypeScript nullability and GraphQL schema compatibility
- verify existing storefront utilities/patterns were reused where appropriate

For storefront accessibility changes, always verify:

- interactive elements have correct accessible names
- dialogs have non-empty accessible names
- `aria-labelledby` and `aria-describedby` point to existing non-empty content
- visually hidden labels/descriptions still work for assistive tech
- keyboard-only flow is usable
- focus is trapped/restored where expected
- live regions announce meaningful changes
- empty string does not bypass fallback logic

### 3. Runtime Review With Playwright

Use Playwright CLI when a review URL or local storefront is available and the PR/MR affects user-facing frontend behavior.

Prioritize high-risk scenarios from the risk map. Prefer semantic assertions over screenshots:

- `getByRole(..., { name: ... })`
- accessible description checks
- keyboard-only navigation
- focus restoration after closing dialogs/popups
- form validation and error state checks
- cart/checkout/login/product detail happy path where relevant
- mobile viewport check when layout or navigation changed

Screenshots are useful for visual regressions, but they are not enough for accessibility review.

If Playwright or the review environment is unavailable, keep reviewing statically and list runtime checks under `Not verified`.

### 4. Test Coverage Audit

Map changed behavior to tests.

Flag missing or weak coverage when:

- behavior changed but no assertion protects it
- a large accessibility/user-flow change only updates snapshots or unrelated tests
- tests assert implementation details instead of user-visible behavior
- tests do not cover critical edge cases found during call-site tracing
- a regression would not fail any Vitest/Cypress/Playwright assertion

Suggest the smallest useful test that would catch the issue.

### 5. Findings Rules

Report only findings backed by evidence.

Each finding must include:

- file and line
- what is wrong
- why it matters
- reproduction path or reasoning path
- minimal fix
- suggested test when relevant

Severity:

- `Critical`: broken user flow, security issue, data loss, incorrect checkout/cart/pricing/auth behavior, WCAG-impacting accessibility regression
- `Warning`: likely bug, missing important tests, performance risk, maintainability issue, fragile edge case
- `Suggestion`: optional improvement with low risk

## Output Format

```markdown
## Task scope
- Jira/PR-MR intent summarized in a few bullets.

## Risk map
- High-risk changed areas and why they matter.

## Critical findings
- [file:line] Finding, impact, evidence, minimal fix, suggested test.

## Warnings
- [file:line] Risk, evidence, recommended fix, suggested test.

## Suggestions
- [file:line] Optional improvement.

## Test coverage audit
- Changed behavior covered by tests.
- Changed behavior not covered by tests.
- Tests that should be added or strengthened.

## Playwright verification
- Review URL:
- Tested scenarios:
- Result:

## Not verified
- Jira/runtime/Playwright/environment checks that could not be performed.

## Verified correct
- Important high-risk behavior that was checked and appears correct.

## Summary
- Concise verdict and top remaining risks.
```

If there are no findings, explicitly say that no actionable issues were found and list what was verified.
