---
name: generate-upgrade-notes
description: Generates structured upgrade notes for a pull request based on diff analysis.
---

# Generate Upgrade Notes

Generates upgrade notes for Shopsys Platform features when creating pull requests. Analyzes code changes and creates structured upgrade note files following project conventions.

## Initial Setup

When invoked, respond:
```
I'm ready to generate upgrade notes. Please provide:
1. Link to the pull request (or I can analyze current branch changes)
2. Scope: backend, storefront, or both (or I can infer from changes)
```

Then wait for user input.

## Command Arguments

- **[link-to-pull-request]** (optional): GitHub PR URL or number (e.g., `4183` or `https://github.com/shopsys/shopsys/pull/4183`)
- **[backend|storefront|both]** (optional): Scope of changes - will be inferred if not provided

## Workflow After Receiving User Input

Use TodoWrite to track: fetching PR data → analyzing changes → generating files → user review

### Step 1: Parse User Input

Extract from user input:
- **PR identifier**: URL, number, or "current-branch"
- **Scope** (optional): backend, storefront, or both

### Step 2: Fetch PR Data (Using Subagent)

**Launch the pr-diff-fetcher subagent:**

Use the Task tool with general-purpose agent:

```
Description: "Fetch PR diff and metadata"

Prompt: "You are the PR Diff Fetcher subagent. Read the specification at .agents/skills/pull-request-diff-fetcher/SKILL.md and follow it exactly.

Input: {PR_URL or PR_NUMBER or 'current-branch'}

Your task: Fetch complete PR data (diff, metadata, commits) using the best available method (gh CLI → WebFetch → local git) as specified in the helper documentation.

Return structured results following the output format in the specification."
```

**Wait for subagent results.**

If subagent requests user input (e.g., "What is the base branch?"), relay the question to user and pass answer back.

### Step 3: Analyze Changes (Using Subagent)

**Launch the upgrade-notes-analyzer subagent:**

Use the Task tool with general-purpose agent:

```
Description: "Analyze BC breaks and movements"

Prompt: "You are the Upgrade Notes Analyzer subagent. Read the specification at .agents/skills/upgrade-notes-analyzer/SKILL.md and follow it exactly.

Input:
- Complete diff content: {from pr-diff-fetcher}
- Commit messages: {from pr-diff-fetcher}
- PR metadata: {from pr-diff-fetcher}
- User-specified scope: {user input or 'infer'}

Your task: Analyze the diff following the critical three-step process:
1. Detect movements FIRST
2. Identify true deletions (not movements)
3. Find modifications and BC breaks

Return structured analysis following the output format in the specification, including ready-to-use upgrade note content."
```

**Wait for subagent results.**

### Step 4: Generate Upgrade Note Content

Based on analyzer output, prepare the upgrade note file content:

**File naming:**
- Format: `{scope}_YYYYMMDD_HHmmss.md`
- Get timestamp: `date +"%Y%m%d_%H%M%S"`

**Content structure:**
```markdown
#### {PR Title} ([#{PR_NUMBER}](https://github.com/shopsys/shopsys/pull/{PR_NUMBER}))

{Content from analyzer subagent - already formatted}

```

**Decision tree for content:**
1. **BC breaks found** → Use detailed content from analyzer
2. **No BC breaks BUT project-base changed** → Minimal note: title + "see #project-base-diff"
3. **No BC breaks AND no project-base changes** → Minimal note: title only

**Files to create:**
- **backend scope** → `upgrade-notes/backend_YYYYMMDD_HHmmss.md`
- **storefront scope** → `upgrade-notes/storefront_YYYYMMDD_HHmmss.md`
- **both scope** → Create BOTH files with scope-specific content

### Step 5: Present to User for Review

**Show comprehensive summary:**

```markdown
## Analysis Summary

**PR:** #{PR_NUMBER} - {title}
**Base branch:** {branch}
**Method used:** {gh|webfetch|local-git}

### Changes Detected
- **Files changed:** {count}
- **Feature movements:** {count} (project-base → packages)
- **Breaking changes:** {count}
- **Project-base changes:** {yes/no}

### Movements Found
{list movements if any}

### Breaking Changes Found
{list BC breaks if any, or "No breaking changes detected"}

### Scope
**Determined scope:** {backend|storefront|both}
{reasoning if inferred}

---

## Generated Upgrade Notes

### File: upgrade-notes/{scope}_YYYYMMDD_HHmmss.md

```markdown
{show complete file content}
```

{if both scope, show second file}

---

Would you like me to:
1. Save this as-is
2. Make edits (please specify changes)
3. Cancel
```

### Step 6: Handle User Response

**If user requests edits:**
- Make requested changes
- Show updated content
- Ask for confirmation again

**If user approves:**
- Proceed to Step 7

**If user cancels:**
- Confirm cancellation
- Do not create files

### Step 7: Save the Files

After user confirms:

1. Create file(s) using Write tool in `upgrade-notes/` directory
2. Confirm successful creation

**Final message:**
```
✓ Created upgrade notes:
  - upgrade-notes/{scope}_YYYYMMDD_HHmmss.md
  {- upgrade-notes/{scope2}_YYYYMMDD_HHmmss.md if both}

Ready for inclusion in your PR. These will be combined into UPGRADE-{version}.md during release.
```

## Subagent Architecture

This command uses two specialized subagents (via general-purpose agent with detailed prompts):

### 1. PR Diff Fetcher (`.agents/skills/pull-request-diff-fetcher/SKILL.md`)
**Responsibilities:**
- Check gh CLI availability and authentication
- Fetch PR metadata (base branch, title, labels)
- Get complete diff using best method (gh → WebFetch → local git)
- Fetch commit messages
- Handle fallback logic

### 2. Upgrade Notes Analyzer (`.agents/skills/upgrade-notes-analyzer/SKILL.md`)
**Responsibilities:**
- Detect feature movements (FIRST!)
- Identify true deletions vs movements
- Find BC breaks (removals, modifications)
- Determine scope
- Generate ready-to-use upgrade note content

## Error Handling

**If pr-diff-fetcher fails:**
- Show error from subagent
- Suggest alternatives (try different method, check authentication, etc.)
- Allow user to retry or cancel

**If upgrade-notes-analyzer finds issues:**
- Show warnings/concerns from analyzer
- Ask user for clarification if needed
- Proceed with best-effort analysis

**General errors:**
- Invalid PR link → Ask user to verify
- No changes detected → Inform user, ask to verify
- Unable to determine scope → Ask user to specify
- Diff too large/truncated → Warn user, use best available data

## Architecture Awareness

**Package-first development:**
- Core logic in `/packages/`
- Configuration in `/project-base/`
- Feature movements from project-base to packages are significant architectural changes

**Key file locations:**
- Backend: `/packages/framework/`, `/packages/frontend-api/`, `/project-base/app/src/`
- Storefront: `/project-base/storefront/`
- Config: `/project-base/app/config/`

## Quality Checks

- **Upgrade notes are instructions, not a changelog** — every bullet must tell the developer what to DO, not describe what was built
- **Never restate what `#project-base-diff` already shows** — a change that consists of editing a project-base file (config yaml, ES definitions, storefront code) must NOT become its own bullet, and never paste config snippets from the diff
- Bullets are reserved for exactly three categories:
    1. package-level BC breaks (signatures, removals, renames) — invisible in the project-base diff
    2. manual actions invisible in ANY diff (index recreation, exports, cache clears) — phrase them as "after applying the project changes, do X"
    3. conditional decisions the developer must make
- Drop speculative "if you customize Y" warnings when the PR did not touch Y
- Never list new features, additions, or descriptions unless they require developer action
- Focus exclusively on breaking changes requiring manual action
- Avoid documenting changes caught by static analysis
- Always include `#project-base-diff` phrase when project-base changes
- Use FQCN (Fully Qualified Class Names) everywhere
- Before writing each line, ask: "Does the developer need to change their code because of this?" — if NO, omit it

## Example Real-World Patterns

The analyzer subagent has comprehensive examples of upgrade note patterns. Key patterns include:

1. **Simple project-base only** (no BC breaks)
2. **Method/property removal** with replacement
3. **Constructor/method signature changes**
4. **Feature movements** from project-base to packages
5. **Conditional instructions** ("if you have X")
6. **Database/infrastructure** manual actions
7. **GraphQL schema changes**
8. **Storefront changes** (components, hooks, types)
9. **Configuration changes**
10. **Multiple related changes**

See `.agents/skills/upgrade-notes-analyzer/SKILL.md` for detailed examples.

## Example Usage Scenarios

**With PR link and scope:**
```
User: /generate-upgrade-notes https://github.com/shopsys/shopsys/pull/4183 backend
```

**With just PR number:**
```
User: /generate-upgrade-notes 4169
```

**Current branch analysis:**
```
User: /generate-upgrade-notes
```

**Both scopes:**
```
User: /generate-upgrade-notes 4135 both
```
