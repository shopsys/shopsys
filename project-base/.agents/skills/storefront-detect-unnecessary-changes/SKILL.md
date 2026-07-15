---
name: storefront-detect-unnecessary-changes
description: Audits current storefront worktree or branch changes for code and artifacts that are unnecessary, dead, speculative, equivalent, redundant, or unrelated. Use before committing storefront changes, when simplifying a frontend diff, or when the user asks whether anything can be removed from a storefront implementation.
---

> **Monorepo note:** if the repository has top-level `packages/` and `project-base/` directories (the shopsys/shopsys monorepo layout, not a standalone project), also read `.agents/skills/monorepo-vs-project/SKILL.md` and apply its delta (package-first + path/role remap) on top of this skill.

# Storefront Detect Unnecessary Changes

Review storefront changes immediately before committing them and identify only changes that can be proven unnecessary. Keep this audit separate from committing and from full PR review.

## Safety

Run this skill read-only. Do not edit, discard, stage, unstage, commit, or push changes. Do not continue with the commit skill automatically; report the result so the user can decide how to handle each finding first.

Preserve unrelated worktree changes and distinguish staged, unstaged, and untracked files in the report.

## 1. Establish Scope And Intent

- Read `AGENTS.md` and relevant scoped instructions.
- Inspect `git status --short`, the staged diff, the unstaged diff, and relevant untracked storefront files.
- If the user supplies paths, a commit range, or a base ref, use that scope. Otherwise audit all current storefront changes and label their staged state.
- Derive required behavior and invariants from the user request, Jira issue, approved plan, documentation, and existing behavior. When a Jira URL or key is provided or discoverable, fetch it first and summarize the task scope.
- If intent cannot be established, continue where possible and record the missing context under `Not verified`; do not guess that a change is unnecessary.
- Identify generated files. Audit the source change and regeneration path rather than suggesting hand-written cleanup of generated output.

## 2. Inventory Changed Constructs

Inspect the changed source files in enough context to understand their behavior. Inventory newly added or changed:

- components, props, callbacks, refs, options, hooks, wrappers, state, contexts, and stores
- GraphQL operations, fields, fragments, generated types, and cache logic
- test IDs, translations, styles, Tailwind classes, and design abstractions
- dependencies, configuration, utilities, tests, fixtures, and snapshots

Focus on additions and contract changes, but include suspicious rewrites or deletions when they create redundant replacement logic.

## 3. Trace Necessity

For every suspicious construct:

- Trace it from declaration through pass-through layers to real consumers with `rg`.
- Inspect representative callers and runtime branches; distinguish an unused public contract from internal state, refs, or callbacks that still implement required behavior.
- Compare with the relevant base version to find no-op rewrites, equivalent values, duplicated behavior, and unrelated scope expansion.
- Check existing storefront utilities and patterns before accepting a new abstraction.
- Check documentation, upgrade notes, configuration-driven usage, dynamic references, and extension patterns before calling an apparently unused API dead.
- Consider multi-domain, multi-language, authenticated and unauthenticated states, empty values, loading and error states, and SSR/CSR boundaries where relevant.

Do not classify a change as unnecessary only because it lacks a direct test, follows a different style preference, or is not exercised by the most obvious caller.

## 4. Classify Evidence

Classify each inspected suspicious change as:

- **Required** — supports a requirement, invariant, real caller, or verified behavior.
- **Dead or speculative** — has no real consumer or documented extension contract.
- **Equivalent or redundant** — does not change behavior or duplicates an existing mechanism.
- **Unrelated** — is valid in isolation but does not belong to the intended change set.
- **Ambiguous** — may be intentional, but evidence is insufficient.
- **Generated** — comes from generated output; assess its source and regeneration path.

Report actionable findings only for proven dead/speculative, equivalent/redundant, or unrelated changes. For each finding, give the exact file and line, evidence, impact on the change set, and the smallest safe simplification.

Keep ambiguous items out of actionable findings and state what context would resolve them.

## Output

```markdown
## Scope reviewed
- Intent and inspected staged, unstaged, untracked, or branch changes.

## Proven unnecessary changes
- [file:line] Classification, evidence, and smallest safe simplification.

## Ambiguous changes
- [file:line] Missing evidence needed to decide.

## Required changes checked
- Suspicious-looking changes that were traced and found necessary.

## Not verified
- Missing task, runtime, generated-source, or extension context.

## Verdict
- Whether the change set contains proven unnecessary changes and whether it is ready for the separate commit step.
```

If no actionable findings exist, explicitly say that no proven unnecessary changes were found and list the evidence checked. Do not claim that the implementation is generally correct; this skill audits necessity, not full correctness.
