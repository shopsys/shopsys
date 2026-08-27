---
name: commit
description: >
  Interactive commit workflow — analyzes all uncommitted changes, proposes atomic, logically
  grouped commits with messages following commit-conventions, and executes them only after
  the user approves the groups. Use when the user asks to commit their current changes,
  split the working tree into commits, or invokes /commit. For just a commit message or a
  single amend/reword, commit-conventions alone is enough.
user_invocable: true
version: 2.0.0
---

# Commit Command

Analyzes uncommitted changes and creates logical, atomic, grouped commits. Every group and every
message follows `.agents/skills/commit-conventions/SKILL.md` — read it first; this file only
describes the interactive flow around those rules.

## Workflow

### Step 1: Gather Changes

```bash
git status                       # staged, unstaged, and untracked files
git diff --stat                  # unstaged changes summary
git diff                         # unstaged changes detail
git diff --cached --stat         # staged changes summary
git diff --cached                # staged changes detail
git diff --name-status           # detect renames, moves, deletions
git diff --cached --name-status  # same for staged
```

**Warning:** Skip sensitive files (`.env`, credentials, API keys) — warn user if detected.

### Step 2: Analyze and Group

Group the changes into **atomic, functional units** following the "Atomic Commits" section of
`commit-conventions` (tests with their code, lockfiles and migrations with their cause, generated
files and upgrade notes handled as described there, order so that no commit breaks the app).

If a single file contains changes for multiple groups, use `git add -p <file>` to stage specific
hunks separately.

### Step 3: Propose Commit Groups

Present groups to user in format:

```
## Group 1: admin: product list now displays name instead of ID

- improves readability for administrators managing large catalogs

Files:
- path/to/file1.php
- path/to/file2.php

---

## Group 2: OrderTotalCalculator: added null guard for missing discount

- order total crashed when optional discount was not set

Files:
- path/to/other-file.php
```

Then ask: **"Proceed with these groups? (yes / edit messages / modify groups / regroup / abort)"**

### Step 4: Execute After Approval

For each approved group:

1. Stage only the files in that group (use `git add -p` for partial files)
2. Commit with the approved message
3. Report success
4. Move to next group

**If commit fails** (pre-commit hook, etc.):

- Show the error
- Ask user how to proceed (fix and retry / skip this group / abort)

**If user wants to abort mid-process:**

- Stop immediately
- Inform user which commits were already made
- Suggest `git reset --soft HEAD~N` to undo if needed

## Interaction Pattern

1. Show proposed groups with suggested messages
2. Ask user to confirm, edit messages, modify groups, regroup, or abort
3. Only commit after explicit approval
4. Report success after each commit
5. If all changes belong to one logical group, propose single commit
