---
name: rebase-autosquash
description: >
  Rebase a branch onto the latest base branch and apply its fixup commits
  (git rebase --autosquash), resolving conflicts semantically along the way. Works on
  a pull request (rebases its head branch, force-pushes with --force-with-lease only,
  and posts a report comment) or on the currently checked-out local branch — then it
  stays local: no push, no PR comment, pushing is left to the user. Verifies the
  result with git range-diff. Use when a PR is stale, blocked by
  fixup commits, or conflicts with its base, when the user asks to rebase and apply
  fixups on the current branch, or when the user invokes /rebase-autosquash. Runs
  locally or in CI (wrapped by the rebase-pr workflow).
user_invocable: true
version: 1.0.0
---

# rebase-autosquash (monorepo)

The rebase itself — autosquash, conflict resolution, range-diff verification — is
authored from the project perspective as a pure-git flow. **Read the canonical:**

- `project-base/.agents/skills/rebase-autosquash/SKILL.md`

Then apply the monorepo delta (`.agents/skills/monorepo-vs-project/SKILL.md`). The
monorepo (GitHub-hosted, `gh` available) adds a **PR mode** on top:

## Mode selection

- **PR mode** — a PR reference was given (bare number, full URL, or
  `owner/repo#number`), or running in CI: the flow below applies. These are the
  only two ways into PR mode.
- **Branch mode** — no PR reference, running locally: follow the canonical on the
  currently checked-out branch, resolving the base per the canonical (prefer
  `gh repo view --json defaultBranchRef --jq .defaultBranchRef.name`). Branch mode
  is strictly local: do not look up whether the branch has an open PR, never push,
  and never comment — per the canonical, pushing stays with the user. If the user
  wants the PR updated, they say so (or pass the PR reference to get PR mode).

## PR mode — additions to the canonical

### Preconditions

On top of the canonical's checks, resolve the PR first:

```
gh pr view <number> --json number,state,isDraft,headRefName,baseRefName,headRepository,headRepositoryOwner,title,url
```

Abort with a clear report (no comment, no push) when the PR is not `OPEN`, or its
head branch lives in a fork (`headRepositoryOwner` differs from the base repo
owner) — there is nothing we can push to.

### Prepare

The PR dictates both branches — instead of the canonical's Step 1:

```
git fetch origin <base> <head>
git checkout <head>            # make sure it matches origin/<head>
```

Then continue with the canonical's Steps 1–4 (everything after the fetch —
recording `ORIG_HEAD_SHA`, nothing-to-do check, rebase, conflicts, verification)
unchanged.

### Force-push

PR mode pushes without asking — that is its purpose:

```
git push --force-with-lease origin <head>
```

Only ever `--force-with-lease`, never plain `--force`. If the lease fails, someone
updated the branch in the meantime — abort, report, and do **not** retry with a
fresher lease without re-running the whole flow from the preconditions.

This applies to local runs only — in CI the push is performed by a dedicated
workflow step, not by Claude (see "CI wrapper" below).

### Report

Whenever the branch was pushed, post a PR comment (`gh pr comment`) in English
carrying the canonical's report content (base tip, old → new head SHA, applied
fixups, per-conflict resolutions, dropped/skipped commits), closing with a note that
the author should review the conflict resolutions before merging (only when there
were any). On failure in CI, post a comment explaining what blocked the rebase and
what needs to be done manually. When run locally, additionally summarize the same
facts in the chat.

## CI wrapper

`.github/workflows/rebase-pr.yaml` runs this skill via `claude-code-action` —
triggered from the Actions tab (`workflow_dispatch` with a PR number) or by a
`/rebase` comment on the PR (write permission required). Differences from a local
PR-mode run:

- The workflow itself hard-gates the trigger (exact `/rebase` command, commenter
  write permission, PR open and not from a fork) before Claude starts — the skill's
  preconditions become a second line of defense.
- `GIT_SEQUENCE_EDITOR=:` and `GIT_EDITOR=true` are preset in the environment, so
  plain `git rebase -i --autosquash --empty=drop origin/<base>` works without the
  env prefix.
- Claude neither pushes nor comments: it stops after the canonical's verification
  and writes the report body to the file named in the prompt. A deterministic
  workflow step then force-pushes the fixed `origin <head>` refspec (after its own
  safety checks — clean tree, rebased onto the base, no fixup subjects left) and
  posts the report as the PR comment.
