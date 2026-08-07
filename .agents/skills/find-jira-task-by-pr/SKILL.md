---
name: find-jira-task-by-pr
description: >
  Find the SSP Jira issue that belongs to a pull request (given a PR number or URL) and
  report its key details — status, sprint, assignee, and the Merge Request link. Use
  when the user asks which Jira issue tracks a PR, or invokes /find-jira-task-by-pr.
user_invocable: true
version: 1.0.0
---

# Find Jira Task by PR

Resolves the SSP Jira issue belonging to a pull request and reports what it found.
Read-only — never creates, edits, or transitions anything. Requires a connected Jira
MCP server.

## MCP tool names — two servers exist

Use whichever set is available:

| Action       | Official Atlassian server (local) | `mcp-atlassian` / sooperset (CI) |
| ------------ | --------------------------------- | -------------------------------- |
| Search (JQL) | `searchJiraIssuesUsingJql`        | `jira_search`                    |
| Read issue   | `getJiraIssue`                    | `jira_get_issue`                 |

The official server additionally needs `cloudId: shopsys.atlassian.net` on every call;
the sooperset server does not (the site is fixed by its `JIRA_URL` env).

## Command Arguments

- **PR reference** (required): a bare PR number, or a full PR URL for the rare case
  the PR lives outside `shopsys/shopsys` (e.g. `https://github.com/shopsys/deployment/pull/42`).

## Field reference (SSP project)

| Field         | Id                  | Content                    |
| ------------- | ------------------- | -------------------------- |
| Sprint        | `customfield_10020` | sprint entries with state  |
| Merge Request | `customfield_10031` | the pull request URL       |

## Workflow

### Step 1: Canonical PR URL

A full PR URL is used as-is. A bare number always means
`https://github.com/shopsys/shopsys/pull/<number>` — that is where the pull requests
tracked in SSP live.

### Step 2: Find the issue

1. Primary lookup — exact match on the Merge Request field:
   `project = SSP AND cf[10031] = "<PR URL>"`
   requesting `fields: ["summary", "status", "assignee", "customfield_10020", "customfield_10031"]`.
   Use the `=` operator — `~` is rejected on this field type.
2. Multiple matches are possible (several issues can reference the same PR). Some
   issues happen to carry the PR number at the end of the summary (issues created
   ad hoc from a PR are named that way) — if one of the matches does, prefer it and
   report the others as related; otherwise report all matches.
3. Fallback when the field search returns nothing: those same ad-hoc-named issues can
   exist with the Merge Request field left empty, so try
   `project = SSP AND summary ~ "<PR number>" ORDER BY created DESC` — Jira text search
   tokenizes, so post-filter: the summary must **end with** `#<PR number>`
   (e.g. `… #4753` — not `#47531`).

### Step 3: Report

For the matched issue report: key, summary, status, assignee, sprint (name and state
of the `customfield_10020` entries), the Merge Request link, and — always, as its own
line — the full clickable link to the issue detail:
`https://shopsys.atlassian.net/browse/<KEY>`. If the fields came back incomplete from
the search, fetch the issue directly by key first.

Report facts only. The PR number in the summary is just a lookup aid — never comment
on whether a summary carries it or what kind of work the issue represents.

No issue is a valid outcome — report "no Jira issue found for PR #<number>". A failing
MCP call is a connection problem, not "not found" — report which call failed and with
what error.
