---
name: pr-comments
description: >
  Work with GitHub PR review threads — list unresolved comments as a digest (who is waiting
  on whom), reply to threads, and resolve/unresolve them. For reviewers following up on their
  review, and for PR authors working through received review feedback thread by thread
  (fix, reply, resolve). Use when the user asks for unresolved/open PR comments, wants to
  process or reply to review comments, or invokes /pr-comments with a PR number or URL.
  Shows a quick one-line-per-thread summary by default; full mode with verbatim
  conversations and diff context on request or per thread.
user_invocable: true
version: 1.0.0
---

# PR Review Threads

Digest and act on GitHub pull request review threads using the `gh-pr-review` extension.

## Prerequisites

An authenticated GitHub CLI is required — verify with `gh auth status` and stop with instructions if `gh` is missing or logged out.

The preferred toolpath is the `gh-pr-review` extension. Check availability first:

```bash
gh extension list | grep pr-review
```

If missing, offer to install it (ask the user before installing) and explain the trade-off: with the extension the commands below work out of the box; without it the agent must hand-compose GraphQL queries and mutations each time (slower, more tokens, more room for mistakes — thread resolution is GraphQL-only, REST cannot do it).

```bash
gh extension install agynio/gh-pr-review
```

If the user declines, fall back to `gh api graphql`: query `pullRequest.reviewThreads` (filter `isResolved`, page with cursors past 100 threads / 50 comments) and use the `addPullRequestReviewThreadReply`, `resolveReviewThread` and `unresolveReviewThread` mutations with thread node IDs. Compose these queries ad hoc; the rest of this skill (digest format, safety rules) applies unchanged.

## Commands

All commands accept a PR URL as positional argument, or `-R owner/repo --pr N`.

**List unresolved threads with full conversations** (primary command):

```bash
gh pr-review review view <PR-URL-or-number> --unresolved
```

Returns JSON grouped by review. Most review entries have no `comments` key — ignore them; only entries with `comments` matter. Each comment carries `thread_id`, `path`, `line`, `author_login`, `body`, `is_resolved`, `is_outdated`, and replies in `thread_comments`.

**Quick thread index** (IDs and paths only, no bodies):

```bash
gh pr-review threads list <PR-URL-or-number> --unresolved
```

**Enrich with discussion links and diff context** (one cheap REST call; GraphQL/extension output has neither):

```bash
gh api repos/<owner>/<repo>/pulls/<N>/comments --paginate \
  --jq '.[] | {node_id, in_reply_to_id, path, created_at, html_url, diff_hunk}'
```

Join exactly on comment node IDs: run `review view` with `--include-comment-node-id` and match its `comment_node_id` to REST `node_id` (heuristics like `path` + `created_at` collide when a bot posts a batch of comments in the same second). The thread root is the comment with `in_reply_to_id == null`; its `html_url` is the thread's discussion link.

**Reply to a thread:**

```bash
gh pr-review comments reply <PR-URL-or-number> --thread-id <PRRT_...> --body "..."
```

**Resolve / unresolve a thread:**

```bash
gh pr-review threads resolve <PR-URL-or-number> --thread-id <PRRT_...>
gh pr-review threads unresolve <PR-URL-or-number> --thread-id <PRRT_...>
```

## Quick vs. full mode

Two presentation modes over the same fetched data:

- **Quick** (default): one line per thread — `#<n> · path:line — <status> — <TLDR>` — plus a closing count line. No diff, no conversations, no separator lines. Skip the REST enrichment call; `review view --unresolved` alone suffices.
- **Full**: the complete digest described below — diff context, verbatim conversations, links. Use it when the user asks for details/conversations (`/pr-comments 4695 full`, "ukaž mi to celé"), or when walking a PR author through their review feedback thread by thread (there, render each thread in full as it comes up).

In both modes keep the full thread data and the number → `thread_id` mapping from the fetch, so follow-ups like "show #2 in full" (render that single thread in the full format, fetching enrichment lazily if needed), "reply to #3" or "resolve #1" work without re-listing.

## Output format for the digest

The output is rendered as markdown in a terminal — structure it so each thread is a visually bounded block. Number the threads so the user can refer to them ("reply to #2", "resolve #1"). For each thread emit, in this order:

1. **Heading**: a `### #<n> · path:line — <status>` markdown heading (headings render bold in the terminal and are the strongest visual separator; mark `(outdated)` threads). Status is one of:
   - **waiting on user** — last reply is a question/request directed at the user
   - **waiting on <other login>** — user (or a bot) asked, the other party hasn't answered
   - **actionable** — a concrete change request or bot finding nobody has addressed
   - **FYI** — informational note, no action expected
2. **Discussion link**: the thread root's `html_url` (from the enrichment call) as a **bare URL on its own line** — never as a `[text](url)` markdown link; terminals auto-linkify bare URLs but often don't make markdown links clickable.
3. **TLDR**: one sentence — what the thread is about and what unblocks it.
4. **Diff context**: the tail of the root comment's `diff_hunk` (last ~5 lines, ending at the commented line) in a fenced ```diff block, so the user sees what code the thread hangs on without opening the file. Skip it only when the hunk adds nothing (e.g. a file-level comment).
5. **Full conversation**: every comment as its own blockquote (`>`), first line `> **author (date):**`, with a blank line between comments so each one reads as a separate box. Quote people verbatim — do NOT paraphrase, translate, or shorten what they wrote; the user must not need to open GitHub to read a reply. Only the TLDR is the agent's own words.

Separate threads from each other with a literal heavy line on its own line, surrounded by blank lines (do NOT use markdown `---` — the terminal renders it too faintly):

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

Rules:

- **Commit references**: when a comment mentions commit SHAs (fixups etc.), render each as a bare full GitHub URL (`https://github.com/<owner>/<repo>/commit/<full-sha>`) — never truncate to `abc1234…` and never wrap in a markdown link.
- **Thread IDs**: never show `thread_id` to the user in any form. Keep the number → `thread_id` mapping internal so follow-up actions like "resolve #1 and #3" work without re-fetching.

## Reviewer vs. author perspective

The status classification is relative to who is asking — determine the viewer's login first:

```bash
gh api user --jq .login
```

- **Reviewer** (typical when the user authored the review comments): the digest shows what the counterparty still owes and which of the user's threads await a resolve.
- **PR author** (typical when the user owns the PR and the comments come from others): the digest is a work queue. Offer to walk it thread by thread: show the thread → implement the fix in the working copy → after the fix is committed/pushed, offer to reply (conventionally with the fixup commit URL) and to resolve. One thread at a time, confirming each posted reply.

## Second-round review support

When the PR branch is checked out locally (or the user asks "which of these are already fixed?"), compare each actionable thread against the current code at `path:line` and report which threads appear already addressed by pushed commits and only await resolving. Never resolve a thread based on this analysis alone — propose the list and let the user confirm.

## Safety

- Replying and resolving are outward-facing actions on GitHub: always show the exact reply text / thread list and get the user's confirmation before posting, unless the user already dictated the exact text and target in their request.
- Never resolve threads authored by other people just because they look done — resolving is the user's call.
