---
name: storefront-pr-review
description: >
  Reviews storefront pull/merge requests with risk-based static analysis,
  Jira context, project conventions, call-site tracing, test coverage audit, and
  Playwright runtime verification when available. Use when the user asks for a
  frontend/storefront/Next.js PR/MR code review, especially with a GitHub PR or
  GitLab merge request URL, Jira issue URL/key, or review environment URL.
user_invocable: true
version: 1.0.0
---

# storefront-pr-review (monorepo)

The canonical instructions live in project-base. **Read them:**

- `project-base/.agents/skills/storefront-pr-review/SKILL.md`

Then apply the monorepo delta (`.agents/skills/monorepo-vs-project/SKILL.md`). For this skill it means:

- The monorepo lives on **GitHub** — use the `gh` CLI; the GitLab/`glab` branch of the canonical does not apply here.
- Storefront code the canonical calls `storefront/` is `project-base/storefront/` here.
