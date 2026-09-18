---
name: security-audit
description: >
  Shopsys-specific AI security audit of a pull request diff. Reviews changed code for real,
  exploitable security issues against OWASP Top 10 / CWE, grounded in how this repo implements
  each control. Runs the built-in /security-review as a high-confidence baseline, then layers
  Shopsys-specific checks, and posts findings as inline PR comments plus one summary comment. Use in CI on
  pull requests, or locally to audit branch changes.
user_invocable: true
version: 1.0.0
---

# security-audit (monorepo)

The canonical instructions live in project-base. **Read them:**

- `project-base/.agents/skills/security-audit/SKILL.md`

Then apply the monorepo delta (`.agents/skills/monorepo-vs-project/SKILL.md`). For this skill it means:

- **Target:** `$ARGUMENTS`. A PR reference (`owner/repo#number`, a PR URL, or a bare number) means
  audit that PR's diff; empty means audit the current branch's changes against its base branch.
- **There is no GitLab wrapper script here — the diff comes from `gh`.** PR reference: read the diff
  with `gh pr diff <ref>` and metadata with `gh pr view <ref>`. Local run: `git --no-pager diff`
  (and `--stat`) against the base branch.
- **Repo layout:** the backend the canonical calls `app/` is `packages/*` (the framework source,
  editable here) plus `project-base/app/`; the storefront is `project-base/storefront/`. Framework
  classes the canonical locates in `vendor/shopsys/` live in `packages/`.
- **The `/security-review` baseline runs against `origin/HEAD`**, which the GitHub workflow points at
  the PR base branch before starting the agent. In CI the checkout is the PR merge ref with the
  changes committed, so a clean working tree is not a reason to skip the baseline.
- **You post the result yourself — there is no `report-file=` and no wrapper.** Replace the
  canonical's Phase 5 with the GitHub flow below: line-specific findings go inline on the diff, and
  one sticky summary comment carries the counts and everything that is not tied to a single line.

## Phase 5 (GitHub): Post the result

### Inline comments (line-specific findings)

Before posting anything, list the inline comments that already exist on the PR, so re-runs don't repeat findings that were already reported:

```bash
gh api "repos/<owner>/<repo>/pulls/<pr-number>/comments" --paginate \
    --jq '.[] | select(.line != null) | {path, line, body: (.body | split("\n")[0])}'
```

Skip a finding when an existing comment sits on the same `path` + `line` and describes the same issue — match by CWE and root cause, not by verbatim wording, since phrasing varies between runs. Post the finding only when it is new, has moved to a different line, or has materially changed (e.g. the severity was upgraded). The `select(.line != null)` filter deliberately ignores outdated comments on code that has since changed — a finding that reappears on the reworked code deserves a fresh comment.

For each finding that maps to a changed line, post an inline comment on that `file:line` with `mcp__github_inline_comment__create_inline_comment` (`confirmed: true`). Anchor only to lines in the PR diff. Body:

```
**[Critical · CWE-89] SQL injection** — `$sortField` from the request is concatenated into ORDER BY; an attacker controls the query. Confidence: High.
Fix: allowlist the sort column — `match($sortField) { 'name' => 'p.name', 'price' => 'p.price', default => throw ... }`.
```

Lead with `**[Severity · CWE-XXX] Title**`, then why it's exploitable, the confidence, and the exact fix.

### Summary comment (always, one per PR)

Post a single sticky comment — counts by severity, a pointer to the inline notes, and any finding that doesn't map to a single changed line:

```
<!-- claude-security-audit -->
## 🔒 AI security audit

_Advisory review of the PR diff against OWASP Top 10 / CWE — not a merge gate._

**Findings:** Critical 1 · High 0 · Medium 2 · Low 1 — line-specific ones are inline on the diff.

### Not tied to a single line
- [Medium · CWE-770] New GraphQL `articles` list field adds no complexity cost and depth limiting is disabled — a nested query can exhaust the DB. Confidence: Medium. Fix: declare `complexity` on the field / enable `query_max_complexity`.
```

Keep it as one comment across re-runs so the record isn't duplicated. Find the previous summary by its `<!-- claude-security-audit -->` marker and update it in place; create it only when no marked comment exists:

```bash
COMMENT_ID=$(gh api "repos/<owner>/<repo>/issues/<pr-number>/comments" --paginate \
    --jq '[.[] | select(.body | startswith("<!-- claude-security-audit -->"))][0].id')
if [ -n "$COMMENT_ID" ] && [ "$COMMENT_ID" != "null" ]; then
    gh api -X PATCH "repos/<owner>/<repo>/issues/comments/$COMMENT_ID" -f body="<the markdown above>"
else
    gh pr comment <pr-number> --repo <owner>/<repo> --body "<the markdown above>"
fi
```

Never use `gh pr comment --edit-last` — it edits the current user's most recent comment on the PR regardless of which workflow or tool created it, so it can overwrite an unrelated comment posted by the same bot account.

If there are no findings, post only the summary: `<!-- claude-security-audit -->` + `## 🔒 AI security audit` + `No security issues found in the changed code.` — and no inline comments.
