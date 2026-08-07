---
name: jira-pr-issue
description: >
  Find the SSP Jira issue that belongs to a GitHub pull request (ad-hoc convention:
  summary ends with " #<PR number>", PR link in the "Merge Request" field), read and
  report it, and optionally create a test issue to verify write access. Serves as the
  Jira-MCP connectivity test for CI automations and as a reusable building block for
  workflows that need to look up or create issues for PRs. Use when the user asks to
  find the Jira issue for a PR, test the Jira MCP connection, or invokes /jira-pr-issue.
user_invocable: true
version: 1.0.0
---

# Jira Issue for a Pull Request

Resolves the SSP Jira issue belonging to a pull request (per the ad-hoc convention used
by `/adhoc-pr`), reads it, and reports what it found. With the `create-test-issue` flag
it also verifies write access by creating a clearly-marked test issue.

Runs both locally and in CI. Requires an authenticated GitHub CLI (`gh`) and a
connected Jira MCP server.

## MCP tool names — two servers exist

The Jira MCP tools differ by which server is connected; use whichever set is available:

| Action        | Official Atlassian server (local) | `mcp-atlassian` / sooperset (CI) |
| ------------- | --------------------------------- | -------------------------------- |
| Search (JQL)  | `searchJiraIssuesUsingJql`        | `jira_search`                    |
| Read issue    | `getJiraIssue`                    | `jira_get_issue`                 |
| Create issue  | `createJiraIssue`                 | `jira_create_issue`              |

The official server additionally needs `cloudId: shopsys.atlassian.net` on every call;
the sooperset server does not (the site is fixed by its `JIRA_URL` env).

## Command Arguments

- **PR reference** (required): `owner/repo#number`, a PR URL, or a bare number
  (resolved against the current repo).
- **create-test-issue** (optional flag): also create a test issue in SSP to verify
  write access.

## Field reference (SSP project)

| Field         | Id                  | Convention                                  |
| ------------- | ------------------- | ------------------------------------------- |
| Summary       | —                   | `<PR title> #<PR number>` (verbatim title)  |
| Sprint        | `customfield_10020` | active sprint (board 167)                   |
| Merge Request | `customfield_10031` | the GitHub PR URL                           |

## Workflow

### Step 1: Resolve the PR

```bash
gh pr view <number> --repo <owner>/<repo> --json number,title,url,state
```

(`gh pr view` does not understand the `owner/repo#number` form as one argument —
split it into `--repo owner/repo` + the bare number.)

Record the number, title, and URL — the lookup and any created issue derive from them.

### Step 2: Find the issue

1. Search JQL `project = SSP AND summary ~ "<PR number>" ORDER BY created DESC`
   requesting `fields: ["summary", "status", "assignee", "customfield_10020", "customfield_10031"]`.
2. Jira text search tokenizes, so post-filter the results: the matching issue's summary
   must **end with** `#<PR number>` (e.g. `… #4753` — not `#47531`).
3. If nothing matches, fall back to searching the Merge Request field:
   `project = SSP AND cf[10031] ~ "<PR URL>"`. This may fail on some field types —
   treat a JQL error here as "not found", not as a connection failure.
4. No issue is a valid outcome — report "no Jira issue found for PR #<number>" and,
   unless `create-test-issue` was requested, stop.

### Step 3: Read and report the issue

For the matched issue report: key, `https://shopsys.atlassian.net/browse/<KEY>`,
summary, status, assignee, sprint (`customfield_10020` — name and state of the sprint
entries), and the Merge Request link (`customfield_10031`). If the fields came back
incomplete from the search, fetch the issue directly by key first.

### Step 4 (only with `create-test-issue`): Verify write access

1. Create the issue:
   - `projectKey`: `SSP`
   - issue type: `PRG bug`
   - `summary`: `[AI connection test] <PR title> #<PR number>`
   - **no description**, **no sprint**, **no assignee** — it is a disposable test
     artifact and must not appear in the active sprint
   - Merge Request field: `{"customfield_10031": "<PR URL>"}`; if the create call
     rejects that field, create without it and note that in the report
2. Verify by fetching the new issue directly by key (not JQL — search indexing lags)
   and checking the summary and the Merge Request field.
3. Report the created key + URL and remind that the issue is a test artifact to be
   deleted manually. **Never** transition it, and never edit or delete existing issues.

### Step 5: Report

Summarize: PR (number, title, URL) → found issue (all Step 3 fields, or "none") →
created test issue (key/URL, or "not requested"). In CI (detect with the single
command `printenv GITHUB_STEP_SUMMARY` — compound shell tests are blocked by the CI
tool allowlist), also Write the summary as markdown to `jira-mcp-report.md` in the
repository root — a follow-up workflow step publishes it to the job summary. Do not
append to `$GITHUB_STEP_SUMMARY` directly; it lives outside the workspace and the
sandbox blocks the write in non-interactive runs.

## Rules

- **Read-only by default** — the only write is the test issue behind the explicit
  `create-test-issue` flag; never edit, transition, or delete anything else.
- The test issue must be unmistakably marked (`[AI connection test]` prefix) and must
  not be added to a sprint.
- A missing issue for the PR is a normal result, not an error; a failing MCP call is a
  connection problem — report which call failed and with what error.
