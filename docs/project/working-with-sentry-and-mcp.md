# Working with Sentry and MCP servers

This project uses a self-hosted Sentry instance at [sentry.shopsys.cloud](https://sentry.shopsys.cloud)
(organization `shopsys`, default project `shopsys-platform`) to track runtime errors and performance
issues across the storefront (Next.js) and the backend (Symfony).

Errors captured in the `production` environment are automatically forwarded to Jira as new tickets
in the `SSP` project. With the **Sentry** and **Atlassian** MCP servers connected to your AI
assistant, you can investigate any issue end-to-end without leaving the chat — pulling stack traces,
breadcrumbs, suspect commits, and Jira context in a single flow.

This document explains how to enable the MCP servers and walks through two real workflows:
investigating a production bug and reviewing performance / Web Vitals issues.

## MCP server setup

### Sentry MCP

The Sentry MCP server exposes the read API of your Sentry instance through tools like
`list_issues`, `list_issue_events`, `find_releases`, `get_replay_details`, and others.

To enable it in your AI agent's MCP configuration, you need to generate a personal auth token in
your own Sentry account — go to **User Settings → Auth Tokens → Create New Token**. Required
scopes: `org:read`, `project:read`, `event:read`, `member:read`. The token is shown only once at
creation time, so copy it into your MCP config immediately:

```json
{
    "mcpServers": {
        "sentry": {
            "command": "npx",
            "args": ["-y", "@sentry/mcp-server@latest"],
            "env": {
                "SENTRY_ACCESS_TOKEN": "<your-personal-token>",
                "SENTRY_HOST": "sentry.shopsys.cloud",
                "MCP_DISABLE_SKILLS": "seer"
            }
        }
    }
}
```

After connecting, the agent can call tools such as:

- `list_issues` — list/search issues in a project (filter by environment, level, status, etc.)
- `list_issue_events` — pull individual event payloads with stack trace, breadcrumbs, tags
- `find_releases` — list releases and their associated commits
- `get_replay_details` — fetch a Session Replay attached to an event
- `update_issue` — resolve / ignore / assign an issue

When asking the agent for Sentry data, default to project `shopsys-platform` unless you need a
cross-project view.

#### Project scoping

The personal auth token grants the agent the same access to data that you have as a Sentry user —
typically every project in the `shopsys` organization. Two ways to keep the agent narrowly focused:

- **Pass `projectSlug` explicitly in every tool call** (`list_issues`, `list_issue_events`,
  `find_releases`, etc.). The MCP server itself does not enforce a project filter; it only forwards
  whatever scope the call specifies.
- **Generate a narrow-scope token.** In Sentry → User Settings → Auth Tokens you can pick a single
  organization. If you need true project-level isolation (for example when delegating to an agent
  used by multiple teams), create a dedicated Sentry user that is a member of only the relevant
  projects, and generate the token from that account.

The `MCP_DISABLE_SKILLS=seer` environment variable in the configuration above disables Sentry's
Seer auto-fix skill, keeping the agent in predictable read-only territory.

### Atlassian MCP (Jira)

The Atlassian MCP server exposes Jira read/write tools. After authenticating via the official
Atlassian connector, the agent can call:

- `getJiraIssue` — fetch issue details, including the description that contains the Sentry link
- `searchJiraIssuesUsingJql` — search across the project (e.g. `project = SSP AND status = "Sprint backlog"`)
- `createJiraIssue` — create a new ticket (used for performance issues that aren't auto-created)
- `addCommentToJiraIssue` — append findings, fix summaries, or links to the ticket
- `transitionJiraIssue` — move a ticket through the workflow
- `createIssueLink` — link related tickets (e.g. `Relates`, `Blocks`)

## Workflow A — investigating a production bug

The Sentry alert rule **`Shopsys platform - production - Jira auto-create (new errors)`** creates
a Jira ticket in `SSP` for every new production error with `level >= error`. The ticket starts
with a reference to the originating Sentry issue, so the agent can pull the full context from
there.

### End-to-end example

A fresh ticket lands in your sprint backlog with a title such as
**`Error: Domain 'cz.example.prod.shopsys.cloud:443' is not configured`** — auto-created by the
Sentry alert rule.

1. **Open the agent and point it at the ticket:**

    > Look at this ticket and propose a fix.

2. **The agent fetches the ticket through the Atlassian MCP:**

    ```
    getJiraIssue(cloudId, issueIdOrKey="<ticket-key>")
    ```

    From the description it extracts the Sentry issue ID (e.g. `SHOPSYS-PLATFORM-15F`) and the
    direct URL into Sentry.

3. **The agent pulls the runtime context from the Sentry MCP:**

    ```
    list_issue_events(organizationSlug="shopsys", projectSlug="shopsys-platform",
                     issueId="<sentry-issue-id>", limit=5)
    ```

    This returns the latest events with full stack trace, breadcrumbs, request URL, browser/OS
    tags, and the `release` (commit SHA).

4. **The agent identifies suspect commits:**

    ```
    find_releases(organizationSlug="shopsys", projectSlug="shopsys-platform",
                  query="<release-sha>")
    ```

    Returns the GitLab commit(s) associated with the release where the issue first appeared.

5. _(Optional)_ **The agent inspects a Session Replay** if the event has one attached:

    ```
    get_replay_details(organizationSlug="shopsys", replayId="<replay-id>")
    ```

6. **The agent proposes a fix**, makes the code change locally, and opens a merge request. After
   the MR is merged it leaves a brief summary on the ticket:

    ```
    addCommentToJiraIssue(cloudId, issueIdOrKey="<ticket-key>",
                         body="Fix merged in MR !1234. Root cause: ...")
    ```

7. **Lifecycle sync** (when enabled in the Sentry ↔ Jira integration) moves the Sentry issue to
   _Resolved_ automatically once the Jira ticket transitions to _Done_.

### What the agent cannot do

- Approve a deploy or merge a protected branch
- Change Sentry organization-level settings (alert rules, integrations, code mappings)
- Acknowledge alerts on behalf of an on-call engineer

## Workflow B — performance and Web Vitals review

Performance issues (Slow DB query, N+1, frontend Web Vitals) are emitted by Sentry with
`level: info`, so they **do not** trigger the auto-create Jira rule. Treat them as a recurring
review activity, not as inbox-driven work.

### Suggested cadence

Once per sprint (or monthly), have the agent run a structured review:

1. **List performance issues, ranked by frequency:**

    ```
    list_issues(organizationSlug="shopsys", projectSlug="shopsys-platform",
                query="issue.category:performance", sortBy="frequency", limit=20)
    ```

2. **For each of the top issues, pull a sample event:**

    ```
    list_issue_events(organizationSlug="shopsys", projectSlug="shopsys-platform",
                     issueId="<id>", limit=1)
    ```

    The agent identifies the slow span — typically a GraphQL operation, an image transform, or
    a synchronous I/O call.

3. **Create a tracking ticket for issues worth fixing:**

    ```
    createJiraIssue(cloudId, projectKey="SSP", issueTypeName="PRG US",
                    summary="Optimize <slow-operation>",
                    description="Sentry issue: <url>\nP95 duration: ...\n...")
    ```

4. **Link to a tracking parent**, for example a quarterly performance epic, via
   `createIssueLink(..., type="Relates")`.

### Web Vitals specifically

Frontend Web Vitals (LCP, CLS, INP, TTFB) are visible in the Sentry **Performance → Web Vitals**
dashboard rather than as standalone issues. Use the agent to summarize regressions:

> Compare LCP and CLS for the last 7 days vs. the previous 7 days on `cz.shopsys.cloud`. If a
> metric degraded by more than 10%, list the top transactions contributing to the regression.

The agent calls `get_sentry_resource` against the Performance dashboard URL or uses the
performance-related list/search tools to extract the data, then proposes which transactions to
investigate first.

## Reference

- [Sentry Jira integration](https://docs.sentry.io/organization/integrations/issue-tracking/jira/)
- [Sentry suspect commits](https://docs.sentry.io/product/releases/suspect-commits/)
- [Sentry distributed tracing (Next.js)](https://docs.sentry.io/platforms/javascript/guides/nextjs/tracing/)
- [Sentry MCP overview](https://docs.sentry.io/product/sentry-mcp/)
