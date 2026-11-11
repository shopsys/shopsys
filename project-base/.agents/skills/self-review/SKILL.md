---
name: self-review
description: Fresh-eyes review of your OWN finished change before you hand it off or commit it — checks design, simplicity, naming, tests and delivery (upgrade notes, commit messages) against the project conventions, ideally with parallel read-only subagents, then fixes what is real. Use after implementing a plan or change and before reporting it done.
---

> **Monorepo note:** if the repository has top-level `packages/` and `project-base/` directories (the shopsys/shopsys monorepo layout, not a standalone project), also read `.agents/skills/monorepo-vs-project/SKILL.md` and apply its delta (package-first + path/role remap) on top of this skill.

# Self-review

The conventions in `.agents/skills/coding-conventions/SKILL.md` (especially *Design conventions*) are meant to be followed **while** you plan and implement. This skill is the safety net: a second look at your own diff, with eyes that do not share your assumptions, before anyone else sees it. Run it when the implementation is done and the relevant tests and checks pass.

It is not an independent deep review of named files (a `/review`-style command, where the repository has one). Here the target is your own change and the standard is the project conventions.

## 1. Fix the scope

Collect what you changed, including untracked files:

```bash
git status --short
git diff $(git merge-base HEAD origin/<default-branch>)   # resolve the branch: gh repo view --json defaultBranchRef --jq .defaultBranchRef.name
```

Note the changed files per layer (PHP, GraphQL config, generated files, storefront, tests, upgrade notes) — generated files are only checked for consistency with their source.

## 2. Run the reviewers

Spawn one **read-only** subagent per applicable dimension, all in a single message so they run in parallel. Give each: the scope (the diff command and file list), the exact rules to apply (point it at the skill sections below), and the output format. **Do not pass your own conclusions or justify your choices** — a reviewer that is told the design is fine will agree with it. No subagent edits files.

| Dimension | Rules to apply | Look for |
|---|---|---|
| **Design & data model / API** | `coding-conventions` → *Data model*, *API* | a bespoke structure where an existing relation concept fits; JSON or delimited strings holding structured data; Data/DataFactory scaffolding the nearest sibling doesn't have; schema fields or arguments nobody reads; optional inputs nobody omits; internal classes mirrored into the schema |
| **Simplicity & performance** | `coding-conventions` → *Simplicity* | guards for states nobody can create (trace who could); a second flow next to an existing one; single-use helpers; whole-set computation where a small subset is needed; new queries inside loops on request hot paths |
| **Naming & consistency** | `coding-conventions` → *API* (names), *Comments*, *DocBlocks* | names that differ between PHP class, GraphQL type, storefront and tests; ambiguous field names next to each other; sibling patterns not followed; comments that describe unverified scenarios or restate the code; renamed or removed symbols still mentioned in comments, docs, test names |
| **Tests** | `test-writing`, `backend-tests`, `storefront-tests` | tests that don't exercise their stated behavior; scenario preconditions not asserted; stub-heavy unit tests whose behavior a functional test already covers; magic ids instead of fixture references; names that no longer match the behavior |
| **Delivery** (only if the change has upgrade notes or commits) | `generate-upgrade-notes` → *Quality Checks* and `commit-conventions`, where the repository has them | every symbol in the notes still exists (`git grep`) and every BC change against the base branch is listed or deliberately omitted; commit messages cover all production changes; an independent fix sitting inside a feature commit |

Skip dimensions that don't apply. Each subagent returns a list of findings — `file:line`, the rule, what is wrong, a concrete suggested change — or the single word `none`.

If your agent has no subagents, walk the same dimensions yourself one at a time, re-reading the diff for each as if it were someone else's.

## 3. Triage — verify before you fix

Reviewers can be wrong. Check every finding against the code and sort it:

- **Real** → fix it.
- **False positive** → drop it and keep the reason in one line.
- **Judgement call** (API shape, data model, naming that the user may have opinions on) → do not decide silently; list it for the user.

## 4. Re-verify

After the fixes run only what the touched code needs: the affected tests, PHPStan on the changed files **including tests**, coding standards through phing (see `shopsys-commands`), and `make generate-schema` if GraphQL changed. Re-run the *Delivery* checks if you changed code after writing the upgrade notes or commit messages.

## 5. Report

Keep it short: what you fixed (with the rule it came from), what you rejected and why, and the judgement calls waiting for the user. Do not paste the reviewers' raw output.
