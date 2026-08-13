---
name: acceptance-criteria-check
description: >
  Checks a pull request against the acceptance criteria of its Jira issue by driving the
  deployed review environment with Playwright. Finds the issue whose "Merge Request" field
  holds the PR URL, reads its acceptance criteria, verifies each one in the running
  application (falling back to the diff for criteria that are not visible in a browser), and
  posts one sticky advisory report with screenshots. Use in CI on pull requests, or locally
  against a review URL.
user_invocable: true
version: 1.0.0
---

# acceptance-criteria-check (monorepo)

The canonical instructions live in project-base. **Read them:**

- `project-base/.agents/skills/acceptance-criteria-check/SKILL.md`

Then apply the monorepo delta (`.agents/skills/monorepo-vs-project/SKILL.md`). For this skill it means:

- **There is no `gh` here, and you have no `git` tool either.** The GitLab wrapper script does not
  exist here, and `git diff`/`git log` are deliberately withheld — they accept `--output=<path>`,
  an arbitrary-write primitive that would escape the evidence-directory confinement.
- **Read the diff from the files passed in.** The invocation passes `diff-file=<path>` (the PR's
  changes against its base) and `commits-file=<path>` (its commit messages), both already
  materialised for you. `Read` them; do not try to reconstruct them with a shell command.
- **`Read` is confined to the checkout and the evidence directory, and there is no `Grep`/`Glob`.**
  For Phase 5 code fallback, start from the diff file to see which files changed, then `Read` those
  files directly in the checkout — you cannot search the tree by content here.
- **You do not post the report — you write it.** The invocation passes `report-file=<path>`; write the
  finished markdown there, starting with the `<!-- claude-acceptance-criteria -->` marker. A separate
  workflow job publishes it as the sticky comment and copies it into the job summary. You have no
  permission to comment on the repository, by design. Writing no file means nothing is published.
- **Do not link or embed the screenshots yourself.** That publishing job collects every `.png` you
  left beside the report into the run's evidence artifact and links it under the comment. So name
  the files well and refer to them by bare filename in the "What happened" column when it helps — the
  reader will find the image in the artifact. Any URL you invent for them would be wrong.
- **The review URL is passed as `review-url=<url>`.** Use it; do not go looking for the value
  elsewhere, and in particular do not go reading the process environment for it — see the
  canonical's rule about that.
- **Phase 1 is already a skill** — invoke `find-jira-task-by-pr` with the PR number instead of
  writing the JQL yourself. It owns the SSP field ids, the exact-match operator, and the
  summary-suffix fallback.
- Storefront and application code the canonical calls `storefront/` and `app/` are
  `project-base/storefront/` and `project-base/app/` here; demo fixtures are
  `project-base/app/src/DataFixtures/Demo/`.
- There is no `set-review-url-to-jira.sh` here and the Review URL field is never populated, so the
  canonical's `customfield_10032` fallback does not apply. Without `review-url=` there is no review
  environment to fall back to — skip the runtime checks and say so.
- The review domains are the three the `review` job posts into the PR description: domain 1 at
  the review URL, domain 2 at `cz.` + that host (note `cz.`, not the `cs.` the canonical's example
  shows), domain 3 at the review URL + `/sk`.
- **This repository is public, and so is the Actions log.** The canonical tells you to keep
  identifiers out of the report and put them in the job log instead — that split does not exist
  here. `show_full_output` is normally off so your own output stays out of that log, but it gets
  switched on to diagnose failures, and you cannot tell which run you are in. Assume everything you
  write is world-readable: keep the issue key, tracker links, `*.odin.shopsys.cloud` review URLs,
  branch names and demo account names out of **everything**, not just out of the report.
