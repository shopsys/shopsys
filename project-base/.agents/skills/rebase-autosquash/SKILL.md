---
name: rebase-autosquash
description: >
  Rebase the currently checked-out branch onto the latest tip of its base branch and
  apply its fixup commits (git rebase --autosquash), resolving conflicts semantically
  along the way. Verifies the result with git range-diff; pushing is left to the user.
  Use when the branch is stale, carries fixup commits, or conflicts with its base,
  when the user asks to rebase and apply fixups, or when the user invokes
  /rebase-autosquash.
user_invocable: true
version: 1.0.0
---

# Rebase with Autosquash

Brings the current branch up to date in one pass: rebases it onto the current tip of
its base branch, squashes `fixup!`/`squash!`/`amend!` commits into their targets, and
resolves conflicts. The goal is a branch that is identical in content to the original
plus the base updates — nothing more.

This is a **pure-git flow** — it needs no hosting CLI, so it works the same whether
the project's merge/pull requests live on GitLab or GitHub.

## Command Arguments

- **base branch** (optional): the branch to rebase onto.

## Base branch resolution

1. An explicit base argument wins.
2. Otherwise use the remote's default branch:
   `git symbolic-ref --short refs/remotes/origin/HEAD` (run
   `git remote set-head origin --auto` first if the ref is not set).
3. Never hardcode a branch name — default branches change over time.

## Preconditions — verify before touching anything

Abort with a clear report when:

- the working tree is not clean — never stash or discard the user's work,
- the current branch **is** the resolved base branch — there is nothing to rebase.

## Workflow

### Step 1: Prepare

```
git fetch origin <base>
ORIG_HEAD_SHA=$(git rev-parse HEAD)
git rev-parse origin/<base>
```

Record both SHAs — they go into the final report and the range-diff verification.

If the branch is already based on the tip of `origin/<base>` **and** contains no
`fixup!`/`squash!`/`amend!` commits, there is nothing to do — report that and stop.

### Step 2: Rebase with autosquash

```
GIT_SEQUENCE_EDITOR=: GIT_EDITOR=true git rebase -i --autosquash --empty=drop origin/<base>
```

- `GIT_SEQUENCE_EDITOR=:` accepts the generated todo list (with fixups already moved
  into place) without opening an editor; `GIT_EDITOR=true` accepts commit messages the
  same way.
- `--empty=drop` silently drops commits whose changes already landed in the base —
  list any dropped commits in the report.
- Never edit the todo list beyond what `--autosquash` generates: no reordering, no
  dropping, no rewording.

### Step 3: Resolve conflicts

When the rebase stops on a conflict:

1. Identify the commit being applied (`git status`, `git rebase --show-current-patch`)
   and read its message — it states the intent of the change.
2. For each conflicted file, read the conflict markers **and** enough surrounding code
   to understand both sides: what the commit wanted to change vs. how the base evolved
   (`git log origin/<base> -- <file>` helps for the base side).
3. Resolve so that **both intents survive**. Blanket strategies (`-X ours`,
   `-X theirs`, checking out one side wholesale) are forbidden — every hunk gets an
   informed decision.
4. Handle modify/delete and rename conflicts explicitly: figure out where the code
   moved and apply the change there.
5. `git rebase --skip` is allowed only when the commit's change verifiably already
   exists in the base (check with `git log`/`git show`) — note it in the report.
6. Stage the resolved files and `git rebase --continue`.
7. Keep a per-conflict note (file, commit, one-line description of the resolution)
   for the report.

If the rebase cannot be completed safely, `git rebase --abort`, leave the branch
untouched, and report why.

### Step 4: Verify

All checks must pass before declaring success:

- The rebase finished and the working tree is clean (`git status`).
- No `fixup!`/`squash!`/`amend!` subjects remain in
  `git log --format='%s' origin/<base>..HEAD`. Also skim the final commit messages —
  a squashed `squash!` commit can leave its marker line in a message body; amend it
  away if it does.
- `git range-diff origin/<base> ${ORIG_HEAD_SHA} HEAD` — every difference must be
  explained by (a) the base update itself, (b) a squashed fixup, (c) a conflict
  resolution from Step 3, or (d) a dropped already-applied commit. Anything else means
  the rebase went wrong — investigate before reporting success.

### Step 5: Pushing stays with the user

Do **not** push unless the user explicitly asked for it. When they did, push with
`--force-with-lease` only — never plain `--force`; if the lease fails, someone updated
the branch in the meantime — stop and report instead of retrying. The original commits
stay reachable via `ORIG_HEAD_SHA`/reflog either way, so the rebase is recoverable.

### Step 6: Report

Summarize for the user in the chat:

- the base tip the branch was rebased onto (branch + short SHA),
- previous head SHA → new head SHA,
- how many fixup/squash commits were applied and into which commits,
- each resolved conflict: file, commit, one line on how it was resolved,
- any dropped (already-applied) or skipped commits,
- whether the branch was pushed (and if not, that pushing — typically with
  `--force-with-lease` — is the user's next step).
