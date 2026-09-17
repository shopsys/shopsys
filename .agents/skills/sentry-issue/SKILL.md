---
name: sentry-issue
description: >
  Investigates a Jira issue that Sentry created automatically (or that links a Sentry issue):
  reads the Jira issue, resolves the referenced Sentry issue even when it was deleted or
  regrouped under a newer id, collects the evidence from Sentry (events, request, release,
  occurrence pattern), traces the failing frame to the code, and reports the root cause with
  a classification (bug / expected noise / vendor behaviour / already fixed / needs a
  decision) and a recommended next step. Read-only by default. Use when the user gives a
  Jira key and asks to check, investigate, or find the cause of a Sentry error, or
  invokes /sentry-issue.
user_invocable: true
version: 1.0.0
---

# sentry-issue (monorepo)

The canonical instructions for this skill are authored from the project perspective. **Read them first:**

- `project-base/.agents/skills/sentry-issue/SKILL.md`

Then apply the monorepo delta (package-first + path/role remap):

- `.agents/skills/monorepo-vs-project/SKILL.md`

The method — read Jira, resolve the Sentry issue through the fallback chain, verify the
match on the throwing frame, collect evidence, trace to code, classify, report, and stay
read-only — follows the canonical unchanged. What differs here is where the code lives
and what "already fixed" means.

## Environment delta

- **Frames map to editable source**: the canonical's `vendor/shopsys/<pkg>/src/…` is
  `packages/<pkg>/src/…` here and is the place a framework fix belongs (package-first);
  `app/…` is `project-base/app/…`.
- **Third-party frames live in the root `vendor/…`**, not `project-base/vendor/…` (that
  directory does not exist). The installed version is in the root `composer.lock`
  (`grep -A3 '"name": "<vendor>/<pkg>"' composer.lock | grep version`). Still read-only.
- **"Already fixed" means fixed on the newest framework branch**, not just in the version
  the failing project runs. Check `git log -S'<message or class>' -- packages/ project-base/`
  on the current branch and the newest `X.Y` branch, the `upgrade-notes/` folder, and
  the `ignore_exceptions` list in `project-base/app/config/packages/sentry.yaml`. When a fix
  exists but the reporting project runs an older version, classify as *already fixed* and
  say which version carries it.
- **Documentation and concepts** — use `.agents/skills/docs-researcher/SKILL.md` on the local
  `docs/` instead of docs.shopsys.com.
- **Upstream lookups** — Symfony, Doctrine and other vendor frames: `gh search issues --repo
  <owner>/<repo>` and `gh issue view` work here; cite issue/PR number, title, and state.

## Reporting delta

Same structure as the canonical. In the *Recommendation*, also say **where the fix belongs**
(which package, or `project-base` config only) and whether it needs an upgrade note, so the
issue can be turned into a proper user story or closed as noise without a second look.
