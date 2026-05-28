---
name: release
description: >
  Drives `php bin/console monorepo:release` end-to-end. Runs pre-flight checks, starts
  the releaser, watches for confirm prompts, auto-presses Enter on prompts that are
  safe (waitFor already verified readiness, or ceremonial), and surfaces judgement
  calls to the operator via AskUserQuestion. Use when the user invokes /release.
user_invocable: true
version: 1.1.0
---

# Release Skill

Orchestrates a single stage of the Shopsys release process. The releaser CLI keeps doing the work; this skill drives it and decides what to do at each confirm prompt.

## Invocation

```
/release <version> --stage <stage> --initial-branch <branch> [--resume-step N] [--dry-run]
```

- `<version>` — e.g. `v19.1.0` or `v19.0.1-rc1`.
- `<stage>` — exactly one of `release-candidate`, `release`, `after-release`.
- `<initial-branch>` — e.g. `19.0`.
- `--resume-step N` and `--dry-run` are passed through unchanged to `monorepo:release`.

Reject any other stage with a clear message. The skill does only one stage per invocation.

## Phase 1 — Pre-flight (fail fast, do not auto-fix)

Run all of these; abort with the specific fix instruction if any fail. **Never start/stop containers, never modify Docker state.**

1. `git status --porcelain` empty AND `git rev-parse --abbrev-ref HEAD` equals `<initial-branch>` (release-candidate stage) or the rc branch (release / after-release stages — the branch name is `rc-<webalized-version>`; if unsure, surface it to the operator).
2. **Container git status matches host's**: `docker compose exec -T php-fpm git status --porcelain` is empty too. Host and container can diverge when mutagen ignore patterns affect tracked files, or when files are globally-gitignored on the host but not in the repo. If the container reports changes the host doesn't, fix the divergence first — otherwise the releaser's `git add .` calls inside the container will commit unintended files. For per-clone fixes that don't touch tracked state, append paths to `.git/info/exclude` (mutagen syncs `.git/` so the container sees the same rules).
3. `gh auth token` returns a non-empty token (the value is passed to the releaser via the `--github-token` CLI option, see Phase 2). If `gh auth token` fails, surface and abort.
4. `docker compose ps --status running --format '{{.Service}}'` contains `php-fpm` and `postgres`.
5. macOS only: `mutagen sync list 2>/dev/null | grep -q Watching`.
6. `docker compose exec -T php-fpm test -f /home/www-data/.gitconfig`.
7. `gh auth status` succeeds.

If anything fails, print the offending check and the exact command the operator should run, then stop.

## Phase 2 — Run the releaser

See [Releasing a new version of Shopsys Platform](https://docs.shopsys.com/en/latest/contributing/releasing-a-new-version-of-shopsys-platform/) for the up-to-date manual release process this skill automates.

The releaser is interactive — it prints prompts of the shape `<info>…</info> [<comment>Enter</comment>]` and blocks on stdin. The skill drives it through a named pipe so it can both stream output and inject Enter.

```bash
PIPE="/tmp/release-stdin-$$"
LOG="/tmp/release-out-$$"
mkfifo "$PIPE"
# keep the pipe open for writing so the releaser doesn't see EOF
( while sleep 86400; do :; done ) > "$PIPE" &
KEEPALIVE=$!
docker compose exec -T php-fpm \
    php bin/console monorepo:release <version> \
        --stage <stage> --initial-branch <initial-branch> \
        --github-token "$(gh auth token)" -v \
        [--resume-step N] [--dry-run] \
    < "$PIPE" > "$LOG" 2>&1
```

**Important — do not pipe `tail -f "$PIPE"` into `docker compose exec`.** On macOS, BSD `tail` block-buffers stdout when piped, so a single `echo "" > "$PIPE"` never flushes through to the container and the releaser appears hung at the first prompt. Redirecting docker exec's stdin directly from the FIFO (`< "$PIPE"`) has no buffering layer in between.

Run that with `run_in_background: true`. Then loop:

1. Prefer `Monitor` on the bg bash to stream stdout line-by-line. Fallback: `tail -c +$OFFSET "$LOG"` between iterations, tracking byte offset.
2. Echo every new line to the operator as one-line status (helps them watch from the chat).
3. When you see the prompt line, decide per the table in Phase 3. Press Enter by `echo "" > "$PIPE"`. The prompt line matches either of these (ANSI is stripped under `-T`, so both forms are possible):
   - ANSI-stripped: `^\s*(.+?)\s*\[Enter\]\s*:?\s*$`
   - With Symfony tags: `^\s*<info>(.+?)</info>\s*\[<comment>Enter</comment>\]\s*:?\s*$`
4. Stop when the log contains `Stage "<stage>" for version "<version>" is now finished!`, or when the bg process exits.
5. On exit, run a thorough cleanup. The bg bash exiting doesn't reap its descendants — orphaned `tail`, `sleep 86400`, and `docker compose exec` children get reparented to PID 1 and keep holding the FIFO / docker socket:
   ```bash
   kill $KEEPALIVE 2>/dev/null
   kill $RELEASER_PID 2>/dev/null
   # Sweep any orphans the bg bash didn't reap
   ps -ef | grep -E 'monorepo:release|sleep 86400|tail.*release-stdin' | grep -v grep \
     | awk '{print $2}' | xargs -I{} kill -9 {} 2>/dev/null
   # If the container's PHP releaser is still alive, kill it inside the container too
   docker compose exec -T php-fpm bash -c 'pgrep -f "monorepo:release" | xargs -r kill -TERM' 2>/dev/null
   rm -f "$PIPE" "$LOG"
   ```

If a prompt does not appear in the table, surface it to the operator. Never blindly press Enter on something you don't recognize.

## Phase 3 — Prompt handler table

The releaser prints each step as `<N>/<TOTAL>) <description>` before the worker runs. Use that line + the prompt text together to pick a handler. Categories:

### A. Auto-press Enter

Ceremonial or already-verified prompts. Press Enter without asking.

- `BeHappyReleaseWorker` — after-release final step. Auto-press.
- `CheckCopyrightYearReleaseWorker` — *if* you've already verified the year (Phase 4 side-task). Otherwise surface.
- `CheckLatestVersionOfReleaserReleaseWorker` — compares `utils/releaser/` on `<initial-branch>` against the latest supported release branch (e.g. when releasing `14.5`, diff against the most recent `19.0`-style branch, NOT against `<initial-branch>`). *Auto-press only if* `git diff origin/<latest-branch> origin/<initial-branch> -- utils/releaser/` is empty. Otherwise surface so the operator can backport the latest releaser changes first.
- `ResolveDocsTodoReleaseWorker` — *if* `grep -rln '<!--- TODO' --include='*.md' .` returns nothing. Otherwise surface.

Never auto-press the fallback `confirm("Continue when \"…\" is satisfied")` printed by `waitFor()` after MAX_WAIT_SECONDS — that one means polling gave up. Surface it.

### B. Side-task then surface (skill does the safe automatable work; operator confirms)

Run the listed work, report results, ask the operator to confirm before Enter is sent. **Never post, never commit, never push.**

- `StopMergingReleaseWorker` / `EnableMergingReleaseWorker` / `PostInfoToSlackReleaseWorker` — run the [Slack message draft agent](#slack-message-draft-agent) and print the result in chat. Operator copies and posts to `#sho_group_ssp` manually.
- `EnsureReleaseHighlightsPostIsReleasedReleaseWorker` — `WebFetch https://blog.shopsys.com` and look for the highlights post; report what you found.
- `CheckReleaseBlogPostReleaseWorker` — same probe. The blog post itself has to be drafted in the CMS by the marketing team, not by this skill — only report what's currently published and let the operator follow up.
- `EnsureRoadmapIsUpdatedReleaseWorker` — open the roadmap URL via `WebFetch`, report current state; operator updates Jira manually.
- `UpdateUpgradeReleaseWorker` post-commit sanity check — after the worker commits, run the [UPGRADE sanity-check agent](#upgrade-sanity-check-agent). If clean, auto-press the three `confirm()` prompts in sequence. If issues are found, surface them.
- `UpdateChangelogReleaseWorker` — call `/release-fetch-changelog <version> --target-branch <initial-branch>` to fetch and insert the GitHub-generated notes into `CHANGELOG-<major>.<minor>.md`. On success, report the inserted block and ask the operator to confirm before pressing Enter (the worker runs `phing markdown-fix` and commits after Enter). If the sub-skill fails, fall back to category C (surface only). If the operator wants to audit the auto-generated section/PR classification, batch proposed moves **4 per `AskUserQuestion` call** (the tool's max); "Other Changes" is the catch-all where misclassifications cluster, so start there.

### C. Surface only (judgement call, no useful side-task)

Call `AskUserQuestion` with options `Done — press Enter` / `Pause — exit and resume later`. On Pause, kill the bg process cleanly and tell the operator to re-run with `--resume-step <N>`.

- `SendBranchForReviewAndTestsReleaseWorker`
- `CheckShopsysInstallReleaseWorker` (both stages)
- `VerifyCliIsRunningReleaseWorker`
- `VerifyMinorUpgradeReleaseWorker`
- `MergeReleaseCandidateBranchReleaseWorker`
- `CheckReleaseDraftAndReleaseItReleaseWorker`
- `CreateAndCommitLockFilesReleaseWorker` / `RemoveLockFilesReleaseWorker` (the manual push)
- `CreateAndPushGitTagReleaseWorker` / `CreateAndPushGitTagsExceptProjectBaseReleaseWorker`
- `CheckDocsReleaseWorker`
- `ForceYourBranchSplitReleaseWorker` — pushes the local rc branch (`rc-<webalized-version>`) to origin, then dispatches `monorepo-force-split-branch.yaml` via GH API with `ref` + `inputs.branch_name` = `$this->currentBranchName` (the rc branch). The workflow checks out `refs/heads/<rc>` from origin and runs `split-repositories.sh` against it, so the split repos receive the release-prep commits (CHANGELOG, UPGRADE notes, mutual deps, framework version). The `assert_split_branch_is_not_protected` guard on `^[0-9]+\.[0-9]+$` is fine — `rc-19-0-0`-style names don't trigger it. If the rc push or dispatch fails (auth, conflicts, GH 422), surface immediately with `--resume-step <next>` and pause options.
- `TestYourBranchLocallyReleaseWorker` — surface only. After any test failure, the worker re-prompts `Run the checks again? [yes]:`. Treat each rerun as a fresh round — surface, ask the operator, never auto-press. If CI is green on the same tree but local fails repeatedly, drift in the local env (container state, custom Postgres functions, stale per-package `vendor/`, accumulated Elasticsearch state) is the likely cause; CI is the source of truth and trusting it (skip via `--resume-step <next>`) is a defensible call.
- Anything not listed above — default to surface.

### Polling status lines

`waitFor()` prints `attempt N: <progressDescription>; sleeping Ms`. Echo each to the operator as one short line; do **not** treat them as prompts.

## Phase 4 — Side-task helpers

These are the safe checks the skill runs locally while the releaser is between prompts or during a long `waitFor()`:

- Copyright year: `grep -rE "Copyright[^0-9]{0,20}[0-9]{4}" LICENSE` and compare to `date +%Y`. (Note: the Shopsys License document carries a `version YYYY-MM-DD` line, not a copyright year — there may be nothing to verify; that's fine, the worker just wants acknowledgement.)
- Releaser parity: `git diff --stat origin/<latest-branch> origin/<initial-branch> -- utils/releaser/` where `<latest-branch>` is the most recent supported release branch (e.g. `19.0` at the moment). The point is to bring the latest releaser changes onto older release branches, so always diff against the newest branch, not against `<initial-branch>` itself.
- Docs TODOs: `grep -rln '<!--- TODO' --include='*.md' docs/`.
- Last tag date (for blog-draft input): `git log -1 --format=%cI $(git describe --tags --abbrev=0 origin/<initial-branch>)`.
- **Packages on Packagist missing CI workflows**: `for p in packages/*/; do [ ! -f "$p/.github/workflows/run-checks-tests.yaml" ] && echo "no CI: $(basename $p)"; done`. Any such package that's on Packagist appears in `GithubActionsStatusReporter` as "still pending" forever — the GH-Actions polling check at step 5 will stall until the 2h timeout. Verify each one is listed in `GithubActionsStatusReporter::IGNORED_PACKAGES`; if not, add it before launching.
- **Per-package vendor staleness**: each `packages/*/` may carry its own `composer.lock` + `vendor/`. The framework's test bootstrap typically prefers per-package vendor over root vendor. If a package's `composer.json` constraint was bumped but its lock predates the bump, `phing tests` exercises the older vendor and tests can fail in ways CI never sees. If a specific package's functional/unit tests fail locally while CI is green on the same tree, suspect a stale `packages/<pkg>/vendor/` and move it aside (`mv packages/<pkg>/vendor /tmp/<pkg>-vendor-bak`) so the bootstrap falls back to root.
- **Personal/tool artifacts**: untracked files visible to the container's `git status` (e.g. `.claude/settings.json`, `.playwright-mcp/`) will be picked up by any worker `git add .` and committed. Add to `.git/info/exclude` (per-clone, syncs to container via mutagen):
  ```sh
  printf '\n.playwright-mcp/\n/.claude/settings.json\n' >> .git/info/exclude
  ```

**Mutagen sync reload** (_macOS only_; when you've edited `mutagen.yml`/`mutagen.yml.dist` and need ignore patterns to re-evaluate):
```sh
mutagen project terminate && mutagen project start
```
This reloads config without touching containers. Do NOT run the project's `mutagen-up.sh` — it also performs `docker compose up --force-recreate`, which kills any paused bg releaser.

Report results inline so the operator can decide.

## Constraints

- **Never** start, stop, or `docker compose up/down` containers (per `AGENTS.md`).
- **Never** commit, amend, push, or create tags. The releaser does its own commits; pushes are always operator-driven.
- **Never** post to Slack, Jira, GitHub, or any external system on the user's behalf. Draft → chat → operator copies.
- **Never** auto-press Enter on a prompt that is not explicitly in category A.
- **Never** skip pre-flight failures.

## UPGRADE sanity-check agent

Invoked from category B when `UpdateUpgradeReleaseWorker` finishes its commit and the releaser prints its three `confirm()` prompts. Goal: decide if it's safe to auto-press Enter on all three, or if the operator needs to fix something first.

Spawn a single Agent (subagent_type `general-purpose`) with this prompt, then act on its JSON output.

> You're checking the staged changes to `UPGRADE-<initial-branch>.md` for guideline violations before the release script proceeds. **Important:** `UpdateUpgradeReleaseWorker` runs `phing upgrade-merge` + version replacement + `git add .`, but commits AFTER all three `confirm()` prompts. At the moment this agent runs, the changes are **staged, not yet committed** — inspect via `git diff --cached -- UPGRADE-<initial-branch>.md` (NOT `git show HEAD`). Re-read `docs/contributing/guidelines-for-writing-upgrade.md` first — it's the source of truth. Then examine the staged diff and check:
> 1. **PR-link integrity** — every `#<number>` link to `github.com/shopsys/shopsys/pull/<n>` points to a real merged PR. Use `gh pr view <n> --json state,mergedAt` per link. Closed-without-merge or non-existent → error.
> 2. **Section ordering** — sections in the new block follow the canonical order from the guidelines. Misordered → error.
> 3. **Duplicate docker instructions** — flag any two subsections with near-identical "modify your `docker-compose.yml`" blocks.
> 4. **`#project-base-diff` placeholders** — `grep -n '#project-base-diff' UPGRADE-<initial-branch>.md`; remaining occurrences at the time of the **first** confirm are **expected** (the worker replaces them between confirms 1 and 2) — treat as a warning so the operator knows what's still pending, not an error. If they're still present at the time of confirm 3, then escalate to error.
> 5. **Branch correctness in links** — every URL to `github.com/shopsys/project-base/...` references `<initial-branch>` (or the right tag/commit), not `master`. Wrong-branch → warning.
>
> Return a single JSON object: `{"clean": true|false, "issues": [{"severity": "error"|"warning", "section": "...", "line": N, "message": "..."}], "summary": "..."}`. Nothing else.

After the agent returns:

- **`clean: true`** → press Enter on each of the three confirms as they appear.
- **`clean: false`** → use `AskUserQuestion` with options:
  - "Pause — let me fix" (kill the bg process, tell the operator to fix and resume via `--resume-step <N>`).
  - "Acknowledge and proceed" (operator owns the risk; press Enter on the three confirms).
  - "Show details" (print the `issues` list and re-ask).

## Slack message draft agent

Invoked from category B for the three Slack workers. Produces a single message body that the operator pastes into Slack. The skill never posts to Slack on the operator's behalf.

Spawn a single Agent (subagent_type `general-purpose`) with this prompt, substituting `<variant>` for the worker name:

> Draft a single Slack message for `#sho_group_ssp` about the Shopsys release `<version>` on initial branch `<initial-branch>`, release date `<YYYY-MM-DD>`. Variant: `<variant>`.
>
> - `StopMergingReleaseWorker` — announce that PRs targeting `<initial-branch>` are temporarily frozen for the release cut, and the freeze will be lifted once the release ships.
> - `EnableMergingReleaseWorker` — announce that the freeze on `<initial-branch>` is lifted and merging is allowed again.
> - `PostInfoToSlackReleaseWorker` — announce the release: include the version, a link to `https://github.com/shopsys/shopsys/releases/tag/<version>`, a one-line summary of the headline change (use `gh pr list --base <initial-branch> --state merged --search 'merged:>=<last-tag-date>'` to find it), and a link to `UPGRADE-<initial-branch>.md`.
>
> Keep it short — two to four lines. Plain text only, no code fences, no preamble, no trailing notes. Output the message body and nothing else.

Print the agent's output verbatim to chat, then use the standard category-B confirm — operator pastes the message to Slack and presses "Done — press Enter".
