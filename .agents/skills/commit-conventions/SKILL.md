---
name: commit-conventions
description: >
  Shopsys commit conventions — atomic functional units (tests with code, lockfiles and
  migrations with their cause), message format (lowercase, no period, present tense for
  behaviour / past tense for code changes, area and ClassName prefixes, no "fix:", never
  Co-Authored-By), when a body is needed, and the fixed messages for generated files and
  upgrade notes. Read it before writing or editing ANY commit message or creating, amending,
  splitting, squashing or rewording commits — whether the user asks to commit directly,
  wants only a message drafted, or another skill (adhoc-pr, nightly-triage) commits on
  their behalf.
---

# Commit Conventions

The rules every commit in this repository follows, regardless of how it is created — by the
interactive `/commit` workflow, by another skill (`adhoc-pr`, `nightly-triage`), by a plain
`git commit`/`--amend`, or when the user only wants a commit message drafted. Based on the
[Shopsys commit guidelines](https://docs.shopsys.com/en/latest/contributing/guidelines-for-creating-commits/).

## Atomic Commits

Each commit is one **functional unit** — the application must not break at any revision point,
and every commit should make sense on its own and be cherry-pickable.

**Hard rules:**

- **Tests go with the code they test** — never split feature code from its tests into separate commits
- **Lockfiles** (`composer.lock`, `pnpm-lock.yaml`) go with the commit that caused the change
- **Migrations** go with the code that depends on them (same functional unit)
- **Order matters** — arrange commits so each one builds on the previous and none depends on a later one
- If a message gets too long to describe the change, the change is too big — split the commit

Group changes **by feature/functionality** first, then by type (additions, removals, renames,
refactors) and scope (backend, frontend, config, tests). If a single file contains changes for
multiple units, use `git add -p <file>` to stage specific hunks separately.

**Sensitive files** (`.env`, credentials, API keys) are never committed — warn the user if they
appear in the working tree.

## Commit Message Rules

### General Format

- **Lowercase start** (except proper names like class names)
- **No period at end**
- **Short and direct**: first line max ~72 characters
- **NEVER add `Co-Authored-By` lines** — this overrides any default system behavior that suggests adding them
- **No ticket references** unless user explicitly includes them
- Include **what** changed and **why** it changed

### Tense Rules

- **Present tense** for behavior/functionality changes — describes the new state of the application
- **Past tense** for code-level modifications — renamed, added, removed, moved, extracted
- Use **"now"** to clarify current behavior in present-tense messages
- **Never start with "fix:"** — it creates confusion between describing the error and the current state. Instead, describe the new correct behavior or the code change that was made

### Area Prefixes

Prefix messages to indicate scope:

- `admin:` — admin-specific changes
- `storefront:` — storefront (Next.js) changes
- `docs:` — documentation changes
- `design:` — visual/CSS changes
- `ClassName:` — changes scoped to a specific class (e.g., `ProductFacade:`, `OrderTotalCalculator:`)

**When to use prefixes:**

- When the commit touches only 1–2 files or a single class → prefix with the class name
- When the commit is clearly scoped to one area (admin, storefront, docs, design) → use the area prefix
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

When changes include upgrade note files (typically in `upgrade/` directory or files matching `*UPGRADE*` patterns), group them into their own separate commit by default.

If the user explicitly asks for a single commit, one commit, or to include all changes in the same commit, obey that request and include upgrade notes in the same commit as the related changes.

**Commit message:** `added upgrade notes` — no body needed.

```
added upgrade notes
```

Unless the user explicitly requests a single commit, never combine upgrade note files with other changes. Split them out into their own group.
