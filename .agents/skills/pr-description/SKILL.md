---
name: pr-description
description: >
  Generates a PR description focused on business value using the project's PR template.
  Analyzes branch changes and writes a clear, user-facing explanation of what changed and why.
  Use when the user asks to create/write/generate a PR description, or invokes /pr-description.
user_invocable: true
version: 1.0.0
---

# PR Description Generator

Generates pull request descriptions focused on **business value** — what changed for the user/administrator/developer and why it matters. Avoids describing code-level changes; instead explains the observable impact.

## Command Arguments

- **[PR number or URL]** (optional): Existing PR to update description for
- **[base branch]** (optional): Base branch to diff against (default: auto-detect)

If no arguments provided, analyze the current branch.

## Workflow

### Step 1: Gather Changes

Use the `pull-request-diff-fetcher` skill to fetch PR data (diff, metadata, commits). Invoke it with the PR number/URL or `"current-branch"` if no PR was provided.

The skill handles method selection (gh CLI → WebFetch → local git), base branch detection, and diff completeness verification automatically.

### Step 2: Understand the Changes

Read the diff carefully. For each changed area:

1. **What is the user-visible effect?** — What can an admin/customer/developer now do that they couldn't before? What works differently?
2. **What was the problem or motivation?** — Why was this change needed? What was broken, missing, or inconvenient?
3. **What is the scope?** — Does this affect admin, storefront, API, infrastructure, developer workflow?

Do NOT just list files or describe code modifications. Think about the **business impact**.

### Step 3: Review the PR Title

Evaluate the current PR title (or branch-derived title) against these criteria:

- **Clear and descriptive** — a reader should understand the change without opening the PR
- **Under 70 characters**
- **Starts with a verb or noun** describing the change (e.g., "Add…", "Fix…", "Enforce…")
- **No branch-name artifacts** — slugs like `mg/param-values-uniq` or `feature/xyz` are not good titles
- **No redundancy** with the description — the title frames, the description explains

If the title is poor (e.g., auto-generated from a branch name, too vague, or too long), suggest an improved title. Always present the suggestion to the user — do not apply it without confirmation.

**Examples of bad → good:**

| Bad | Good |
|---|---|
| `Mg/param values uniq` | `Enforce unique parameter values at database level` |
| `fix stuff` | `Fix GoPay cron failing on demo payment transactions` |
| `Feature/admin domain tabs` | `Add direct linking to domain-specific admin pages` |

### Step 4: Write the Description

Load the PR template from `.github/PULL_REQUEST_TEMPLATE.md` and fill in each section with the analyzed content.

Fill the description section with a business-value-focused explanation (1-3 paragraphs covering what changed from the user's perspective, the motivation, and the new behavior). Fill other sections as appropriate (e.g., "closes #123" or "N/A" for fixes, check the license checkbox).

### Step 5: Present and Apply

Show the generated description to the user. If the title needs improvement, show the suggested title as well.

**If updating an existing PR:**

Before replacing the body, check the existing PR description for:
- Content between `<!-- Replace -->` markers (e.g., live preview links) — preserve these blocks exactly as they are.
- Issues already referenced in the "Fixes issues" section (e.g., `closes #123`) — carry them over into the updated description.

These are managed externally and must not be lost.

Ask the user if they want to apply the description (and title if suggested), then:

```bash
gh pr edit {PR_NUMBER} --body "{description}"
gh pr edit {PR_NUMBER} --title "{new_title}"  # only if user confirms
```

**If no PR exists yet:**

Show the description (and suggested title) and tell the user it's ready to use when they create the PR.

## Writing Style

### Do

- Focus on **what changed for the user**, not what changed in the code
- Explain the **problem** that motivated the change
- Describe the **new behavior** clearly
- Use plain language an administrator or product owner would understand
- Keep it concise — 1-3 short paragraphs in most cases
- Use the PR title as context for framing the description

### Don't

- List changed files or classes
- Describe code-level refactoring details (unless that IS the point of the PR)
- Use developer jargon when a plain explanation works
- Pad with unnecessary filler or marketing language
- Repeat information that's already in the PR title

## Examples

### Good: Bug fix

```markdown
#### Description, the reason for the PR

Demo data payment transactions were created with `CREATED` status, causing the GoPay cron job to attempt updating non-existing payment transactions and fail. Changed statuses to final ones (`CANCELED`, `TIMEOUTED`) so the cron skips them.
```

### Good: Feature

```markdown
#### Description, the reason for the PR

This PR makes domain-specific administration pages directly linkable.

Until now, pages with administration domain tabs have always opened in the domain stored in the administrator session. That meant links could lead to the correct agenda, but not to the correct domain tab. In practice, administrators still had to switch the domain manually before they could fix the reported problem.

This change adds support for switching the selected administration domain directly from a link. As a result, links from domain-specific warnings, such as required settings notifications, can open the target page in the exact domain context that needs attention.
```

### Bad

```markdown
#### Description, the reason for the PR

Changed `PaymentTransactionDataFactory.php` to use `CANCELED` and `TIMEOUTED` constants instead of `CREATED`. Also updated `PaymentTransactionData` constructor default value.
```

This is bad because it describes code changes, not the business impact.

## Edge Cases

- **Pure refactoring PRs**: It's OK to describe the technical change, but frame it in terms of developer benefit (easier to extend, less error-prone, etc.)
- **Infrastructure/CI changes**: Describe what improves for the development workflow
- **Multi-concern PRs**: Structure the description with clear paragraphs for each concern
- **Tiny PRs** (typos, annotation fixes): A single sentence is fine
