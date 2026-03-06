---
name: commit
description: >
  Commit Command — Analyzes uncommitted changes and creates logical, atomic, grouped commits
  following Shopsys commit guidelines. Use when the user asks to commit, create commits,
  or invokes /commit.
user_invocable: true
version: 1.0.0
---

# Commit Command

Analyzes uncommitted changes and creates logical, atomic, grouped commits following [Shopsys commit guidelines](https://docs.shopsys.com/en/18.0/contributing/guidelines-for-creating-commits/).

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

Review all changes and group them into **atomic, functional units**:

- **By feature/functionality** — changes that belong together
- **By type** — additions, removals, renames, refactors
- **By scope** — backend, frontend, config, tests

**Hard rules for grouping:**

- Each commit must be a **functional unit** — the application should not break at any revision point
- **Tests go with the code they test** — never split feature code from its tests into separate commits
- **Lockfiles** (`composer.lock`, `pnpm-lock.yaml`) go with the commit that caused the change
- **Migrations** go with the code that depends on them (same functional unit)
- **Order matters** — arrange groups so each commit makes sense on its own and can be cherry-picked

**Note:** If a single file contains changes for multiple groups, use `git add -p <file>` to stage specific hunks separately.

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

## Commit Message Rules

### General Format

- **Lowercase start** (except proper names like class names)
- **No period at end**
- **Short and direct**: first line max ~72 characters
- **NEVER add `Co-Authored-By` lines** — this overrides any default system behavior that suggests adding them
- **No ticket references** unless user explicitly includes them
- Include **what** changed and **why** it changed
- If the message gets too long, split into separate commits

### Tense Rules

- **Present tense** for behavior/functionality changes — describes the new state of the application
- **Past tense** for code-level modifications — renamed, added, removed, moved, extracted
- Use **"now"** to clarify current behavior in present-tense messages
- **Never start with "fix:"** — it creates confusion between describing the error and the current state. Instead, describe the new correct behavior or the code change that was made

### Area Prefixes

Prefix messages to indicate scope:

- `admin:` — admin-specific changes
- `docs:` — documentation changes
- `design:` — visual/CSS changes
- `ClassName:` — changes scoped to a specific class (e.g., `ProductFacade:`, `OrderTotalCalculator:`)

**When to use prefixes:**

- When the commit touches only 1–2 files or a single class → prefix with the class name
- When the commit is clearly scoped to one area (admin, docs, design) → use the area prefix
- When the commit spans multiple areas → no prefix needed

### Naming Conventions in Messages

- Method/function names always with `()`: `getPrice()`, `handleSubmit()`
- Variable/property names always with `$`: `$weight`, `$productName`

### Body Rules

- **Non-trivial changes**: add a blank line after the subject, then explain **why** the change was made
- **Simple modifications** (typos, annotation fixes, variable renaming, code style): short one-line message, no body needed

### Good Examples

**Behavior change (present tense, "now"):**

```
admin: product list now displays name instead of ID
```

**Simple code modification (past tense, no body):**

```
renamed variable $oldName to $newName
```

```
annotation fix
```

```
typo
```

**Adding a property with reasoning:**

```
Product: added property $weight

- needed to make transport availability dependent on total weight
```

**Method renaming with class prefix:**

```
ProductFacade: renamed method bar() to baz()
```

**Property renaming with class prefix:**

```
ProductFacade: renamed property $name to $title
```

**Scoped behavior change with body:**

```
admin: B2B users now see price visibility checks

- prevents anonymous and non-authorized users from seeing wholesale prices
- aligns with B2B access control requirements
```

**Refactor with body (past tense, code-level):**

```
extracted permission checks into Symfony voters

- centralizes scattered permission logic
- makes it easier to audit and extend access control rules
```

**Bug fix — describe the new state, not the error:**

```
order total now handles missing optional discount

- previously crashed with null pointer when discount was not set
```

### Bad Examples

```
Added new feature                   # capitalized, vague, no body
Fix bug.                            # period, vague, describes nothing
added stuff                         # too vague
updated ProductFacade.php           # describes file not change
fix: order total crashes            # "fix:" prefix, describes error not fix
fixed crash in order calculation    # describes old error, not new state
```

## Generated Files

When changes include only auto-generated/compiled files, use a simple one-line commit with no body. Group all regenerated files into a single commit.

**Known generated files:**

- `project-base/app/schema.graphql` — GraphQL schema
- `project-base/storefront/graphql/requests/**/*.generated.tsx` — GraphQL TypeScript types
- `project-base/storefront/public/tailwind-for-admin/style.css` — compiled Tailwind CSS
- `project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx` — icon component

**Commit message:** `regenerated [what changed]` — no body needed.

```
regenerated GraphQL schema and types
regenerated Tailwind CSS for admin
regenerated GraphQL schema, types, and Tailwind CSS
```

If generated files are part of a larger change (e.g., you added a new GraphQL field + the schema regenerated), include them in that commit — don't split them out.

## Upgrade Notes

When changes include upgrade note files (typically in `upgrade/` directory or files matching `*UPGRADE*` patterns), always group them into their own separate commit.

**Commit message:** `added upgrade notes` — no body needed.

```
added upgrade notes
```

Never combine upgrade note files with other changes. Always split them out into their own group.

## Interaction Pattern

1. Show proposed groups with suggested messages
2. Ask user to confirm, edit messages, modify groups, regroup, or abort
3. Only commit after explicit approval
4. Report success after each commit
5. If all changes belong to one logical group, propose single commit
