---
name: adhoc-pr
description: >
  Ad-hoc Pull Request Publisher — commits the current changes, pushes the branch, opens
  a GitHub pull request against the repository's default branch, and creates a matching
  SSP Jira issue in the current sprint following the ad-hoc work convention (summary =
  PR title + " #<PR number>", PR link in the "Merge Request" field, description copied
  from the PR description, moved to In Progress). Works for any ad-hoc change — fixes, small improvements,
  tooling, docs. Use when the user asks to publish/ship the current work end-to-end,
  or invokes /adhoc-pr.
user_invocable: true
version: 1.0.0
---

# Ad-hoc Pull Request Publisher

Wraps the whole "ad-hoc change" flow: commit → push → GitHub PR → Jira issue in the
current sprint. Ad-hoc work (a fix, a small improvement, tooling, docs, …) doesn't get
a spec — the PR is the documentation; the Jira issue only makes the work visible in the
sprint.

Assumes an authenticated GitHub CLI (`gh`) and a connected, authorized Atlassian
(Jira) MCP server — no upfront verification is done; if a step fails on missing
tooling, report what was completed and the user finishes the rest manually.

## Command Arguments

- **[issue type]** (optional): defaults to `PRG bug` for fixes; use `PRG US` for
  improvements/features and `FE bug`/`FE US` for storefront-only work
- **check** (optional flag): also run the full `make check-fix` before committing

The Jira project is always **SSP** — it is not configurable.

## Workflow

### Step 1: Preconditions

```bash
git status --short                                            # what will be committed
git branch --show-current                                     # current branch
gh repo view --json defaultBranchRef --jq .defaultBranchRef.name   # default branch (do NOT hardcode)
git fetch origin <default-branch>                             # make the comparison ref current
```

- If the working tree is clean and the branch is already pushed with an open PR, skip the
  finished steps and continue with the first missing one (idempotent re-run).
- If the current branch **is** the default branch, create a work branch first:
  `<initials>/<kebab-slug>` (initials from `git config user.name`, e.g. `rv/…`).
- The branch must be based on the **default branch** — verify with
  `git log --oneline origin/<default-branch>..HEAD` (against the just-fetched remote ref,
  never a possibly stale local one) that only the intended commits would enter the PR.
  If foreign commits appear, rebase onto `origin/<default-branch>` and remember that a
  rebase happened — Step 3 then needs `--force-with-lease`.

### Step 2: Commit

1. `make check-fix` is **not run by default** — it takes minutes even for trivial
   changes, and the user is expected to have handled code quality beforehand. Run the
   full `make check-fix` only when the user requests it (the `check` argument or an
   explicit ask). If the changes look risky for standards (larger PHP/TS code changes,
   generated files possibly stale), offer it via a quick question rather than running it
   unasked. When it does run: fixes it makes belong in the commit; on failure stop and
   report.
2. Invoke the `commit` skill (Skill tool) to create the commit(s) from uncommitted
   changes. Skip if everything is already committed.

### Step 3: Push

```bash
git push -u origin <branch>
```

If Step 1 rebased an already-pushed branch, push with `--force-with-lease` instead —
never plain `--force`, and never force-push a branch someone else may have built on
without confirming first.

### Step 4: Pull Request

1. Check for an existing PR **before** generating anything:
   `gh pr view --json number,title,url,state`. Reuse it only when `state` is `OPEN` —
   a `MERGED`/`CLOSED` PR belongs to previous work; if the branch's PR was already
   merged, stop and report instead of attaching new work to it.
2. If no open PR exists, invoke the `pr-description` skill to craft the title and
   description (business-value focused, uses `.github/PULL_REQUEST_TEMPLATE.md`).
3. Write the body to a file with the Write tool (e.g. into the scratchpad directory) and
   create the PR with `--body-file` — never inline the body in double quotes, PR bodies
   in this repo routinely contain backticks that the shell would interpret as command
   substitution:

```bash
gh pr create --base <default-branch> --head <branch> --title "<title>" --body-file <path>
```

Record the PR **title**, **number**, and **URL** — the Jira issue is derived from them.

4. **Backfill the PR number into upgrade notes.** Upgrade notes written before the PR
   exists (in `upgrade-notes/`) carry the `{pullRequestId}` placeholder from
   `upgrade-notes/_template.md`. After creating the PR, search the branch for the
   placeholder (`git grep -l pullRequestId upgrade-notes/`), replace it with the real
   PR number, and **amend** the commit that introduced the note (then push with
   `--force-with-lease`) — the placeholder must never survive into the merged history.

### Step 5: Jira Issue

Use the Atlassian MCP tools with `cloudId: shopsys.atlassian.net`.

**Field reference (SSP project, board 167):**

| Field         | Id                  | Value                           |
| ------------- | ------------------- | ------------------------------- |
| Sprint        | `customfield_10020` | numeric id of the active sprint |
| Merge Request | `customfield_10031` | the GitHub PR URL               |

1. **Active sprint id**: search JQL
   `project = SSP AND sprint in openSprints() ORDER BY updated DESC` with
   `maxResults: 1` and `fields: ["customfield_10020"]`; from the first result take the
   `customfield_10020` entry whose `state == "active"` (an issue can carry closed sprints
   too; for SSP the active one has `boardId` 167). If the search returns no issues (fresh
   sprint with nothing in it yet), create the issue **without** the sprint field and tell
   the user to drag it into the sprint manually — do not guess a sprint id.
2. **Assignee**: the requesting user — get `account_id` via `atlassianUserInfo` (or reuse
   a known one from context).
3. **Create the issue** (`createJiraIssue`):
   - `projectKey`: `SSP`
   - `issueTypeName`: `PRG bug` (or the argument)
   - `summary`: `<PR title> #<PR number>` — exactly the PR title, then space and
     `#<number>` (e.g. `Fix expanding lazily loaded branches in admin tree selection #4701`)
   - `description`: **copy of the PR description** (the GitHub PR body, Markdown as-is) —
     the convention is that the Jira issue shows what was done without opening GitHub.
     Fetch it fresh with `gh pr view <number> --json body --jq .body` rather than reusing
     a draft from earlier steps (the pushed PR body is the source of truth). If the body
     contains HTML comments from the PR template, strip them; otherwise do not rewrite or
     summarize the text.
   - `additional_fields`: `{"customfield_10020": <sprintId>, "customfield_10031": "<PR URL>"}`
   - `assignee_account_id`: the user's account id
4. **Fallback**: if creation rejects `customfield_10031` (field not on the create
   screen), create the issue without it and set it afterwards via `editJiraIssue` with
   `fields: {"customfield_10031": "<PR URL>"}`.
5. **Move to In Progress**: the work is already done, so the issue must not sit in the
   backlog column. Call `getTransitionsForJiraIssue` on the new issue, pick the
   transition whose **target status** (`to.name`) is `In Progress` (case-insensitive
   match on the status name, not the transition name), and apply it with
   `transitionJiraIssue`. If no such transition is offered, leave the issue in its
   initial status and report that to the user instead of guessing another status.
6. **Verify**: fetch the new issue directly with `getJiraIssue` (not a JQL search —
   search indexing can lag behind for just-created issues) requesting
   `fields: ["summary", "description", "status", "customfield_10020", "customfield_10031", "assignee"]`,
   and check the sprint, the Merge Request link, the summary suffix, that the description
   is filled (copied from the PR), and that the status is `In Progress`.

### Step 6: Report

Present to the user: commit hash(es), PR URL, Jira issue key + URL
(`https://shopsys.atlassian.net/browse/<KEY>`), and the sprint it landed in.

## Rules

- **Never hardcode the default branch** — resolve it each run; it changes with major
  releases (19.0 → 20.0 → …).
- The Jira summary must **copy the PR title verbatim** and end with ` #<PR number>`.
- The issue **description is a verbatim copy of the PR description** — no summarizing,
  no rewriting; it exists so the reader sees what was done without opening GitHub.
- The PR link belongs in the **Merge Request field** — the copied description carrying
  it too (via the PR template) is fine, but the field is the canonical place.
- Transition the issue to **In Progress** (assigned to the author) — never further; CR,
  testing, and BV statuses are driven by the team workflow, not by this skill.
- If any step fails, stop and report what was already done (commits, PR, issue) so the
  user can pick up manually — do not retry destructively.
