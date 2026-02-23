---
name: pr-diff-fetcher
description: Fetches pull request data including diff, metadata, and commits
tools: Bash, WebFetch, Read
---

# PR Diff Fetcher

This helper is responsible for fetching pull request data including diff, metadata, and commits.

## Input

You will receive ONE of:
- PR URL (e.g., `https://github.com/shopsys/shopsys/pull/4183`)
- PR number (e.g., `4183`)
- String "current-branch" (analyze current git branch)

## Your Task

Fetch complete PR data using the best available method and return structured information.

## Method Priority: gh CLI → WebFetch → Local Git

### Method 1: GitHub CLI (gh) - PREFERRED

**Step 1: Check gh availability**

1. Check installation: `gh --version`
   - If not installed → Skip to Method 2 (WebFetch)
2. Check authentication: `gh auth status`
   - If not authenticated → Skip to Method 2 (WebFetch)
   - If authenticated → ✅ Use gh for everything

**Step 2: Fetch PR data using gh**

1. **Extract PR metadata and BASE branch** (CRITICAL):
   ```bash
   gh pr view {PR_NUMBER} --json baseRefName,title,body,labels
   ```
   - Extract: `baseRefName` (this is your BASE branch - store it!)
   - Extract: `title`, `body`, `labels`

2. **Get complete diff** (PRIMARY DATA SOURCE):
   ```bash
   gh pr diff {PR_NUMBER}
   ```
   - **⚠️ CRITICAL: Verify the diff is COMPLETE before proceeding!**
   - No size limits with gh CLI

3. **Get commits** (for movement detection):
   ```bash
   gh pr view {PR_NUMBER} --json commits
   ```
   - Look for movement keywords: "move", "moved", "moving" + "from project-base" or "to package"

**Return to main command with:**
- Method used: "gh"
- Base branch: {extracted from baseRefName}
- PR title, body, labels
- Complete diff content
- Commit messages
- Success: true

### Method 2: WebFetch with GitHub API (fallback if gh not available)

**Step 1: Extract PR metadata using GitHub REST API**

```
WebFetch: https://api.github.com/repos/shopsys/shopsys/pulls/{PR_NUMBER}
Prompt: "Extract from this JSON: base.ref (base branch name), title, body (description), labels[].name (label names), head.ref (head branch name)"
```

Store the BASE branch (`base.ref`) - you'll need it if diff is large.

**Step 2: Get complete diff**

```
WebFetch: https://patch-diff.githubusercontent.com/raw/shopsys/shopsys/pull/{PR_NUMBER}.diff
Prompt: "Return the complete diff content exactly as-is"
```

**Step 3: Get commits using GitHub REST API**

```
WebFetch: https://api.github.com/repos/shopsys/shopsys/pulls/{PR_NUMBER}/commits
Prompt: "Extract commit messages from the JSON array (commit.message field for each item), looking for keywords: 'move', 'moved', 'moving' combined with 'from project-base' or 'to package'"
```

**Step 4: Decide if local git needed**

- Estimate diff size from WebFetch response
- If diff < 500KB → Use only WebFetch data
- If diff > 500KB OR appears truncated → Need local git

If local git needed, ask main command to confirm with user: "The diff is large. I'll use local git for analysis. Current branch is {branch}. Is the base branch '{extracted_base_branch}'?"

Then use: `git diff {base_branch}...HEAD`

**Return to main command with:**
- Method used: "webfetch" or "webfetch+git"
- Base branch: {extracted}
- PR title, body, labels
- Complete diff content
- Commit messages
- Success: true

### Method 3: Local Git (when no PR provided)

**When input is "current-branch":**

1. Get current branch:
   ```bash
   git branch --show-current
   ```

2. Return to main command with request:
   - "No PR provided. Current branch is {branch}. Please ask user for base branch."

3. After receiving base branch from main command:
   ```bash
   git log {base_branch}...HEAD --oneline
   git diff {base_branch}...HEAD
   ```

**Return to main command with:**
- Method used: "local-git"
- Base branch: {provided by user}
- Current branch: {detected}
- Complete diff content
- Commit messages from log
- Success: true

## Error Handling

If any method fails:
- Try next method in priority order
- If all methods fail, return:
  - Success: false
  - Error: {description}
  - Suggestion: {what user should do}

## Output Format

Always return a structured summary in this format:

```markdown
## PR Data Fetch Results

**Method used:** {gh|webfetch|webfetch+git|local-git}
**PR Number:** {number or "N/A"}
**Base Branch:** {branch name}
**PR Title:** {title}

**Diff size:** {approximate size or "complete"}
**Commits analyzed:** {count}
**Movement indicators found:** {yes/no + details}

**Status:** ✅ Success | ❌ Failed

### Metadata
{JSON structure with all extracted data}

### Diff Content
{First 100 lines of diff as preview}
[Full diff available in metadata]

### Commit Messages
{List of commit messages with movement indicators highlighted}
```

## Important Notes

- **ALWAYS extract and store the BASE branch** - never assume it's "main" or hardcode branch names
- **Verify diff completeness** - incomplete diff = missed changes
- **Check commits for movement keywords** BEFORE returning
- **Prefer gh CLI** - it has no size limits and is most reliable
- **Never make assumptions** - if data is unclear, note it in the output
