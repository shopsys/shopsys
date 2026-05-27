---
name: release-fetch-changelog
description: >
  Calls the GitHub REST API via `gh` to generate release notes for the given tag
  (the same content the "Generate release notes" button produces on the web UI)
  and inserts the result into the matching CHANGELOG-X.Y.md file. Used standalone
  or by the /release orchestrator when UpdateChangelogReleaseWorker is up next.
user_invocable: true
version: 2.0.0
---

# release-fetch-changelog

Automates the GitHub "Generate release notes" copy-paste step using the `gh` CLI. The GitHub-native generator stays the source of truth — this skill does not draft the changelog body itself.

## Invocation

```
/release-fetch-changelog <version> --target-branch <branch> [--changelog-file <path>] [--dry-run]
```

- `<version>` — e.g. `v19.1.0`, `v19.0.1-rc1`.
- `--target-branch <branch>` — e.g. `19.0`. Required.
- `--changelog-file <path>` — override the conventional location `CHANGELOG-<major>.<minor>.md` at repo root.
- `--dry-run` — fetch + show the generated markdown but do not write.

## Pre-flight

Abort with a clear error if any fail:

1. `gh auth status` succeeds.
2. The CHANGELOG file exists at the resolved path. If not, ask the operator to confirm the path with `--changelog-file`.
3. `gh release view <version>` returns "release not found" — bail out if the tag/release already exists.

## Steps

1. **Find previous tag.** Pick the highest **version-sorted** tag strictly less than `<version>`, not the most-recently-published one. Shopsys maintains older majors as LTS lines (e.g. `v14.5.1` was published after `v18.0.0`), so sorting by `publishedAt` would compare a new major against an older LTS patch and produce a misleading diff. Inject `<version>` into the tag list, sort, and print the entry immediately above it (substitute `<version>` with the actual version literal — leaving the placeholder string in place will produce no output because it fails the semver regex):
   ```sh
   { echo "<version>"; gh api repos/shopsys/shopsys/tags --paginate --jq '.[].name'; } \
       | grep -E '^v[0-9]+\.[0-9]+\.[0-9]+$' \
       | sort -V -u \
       | awk -v t="<version>" '$0 == t { print prev; exit } { prev = $0 }'
   ```
   `sort -V` already orders semver tags ascending, so awk just walks the list and prints the line right before `<version>`. The `echo "<version>"` injection makes this work whether or not `<version>` is already in the tag list. For `v19.0.0`, this returns `v18.0.0`, not `v14.5.1`. This is what GitHub's generator uses as the comparison baseline.
2. **Generate notes via `gh`.** Call:
   ```sh
   gh api repos/shopsys/shopsys/releases/generate-notes \
       -f tag_name=<version> \
       -f previous_tag_name=<previous-tag> \
       -f target_commitish=<branch> \
       --jq .body
   ```
   This returns the same markdown the "Generate release notes" button produces on `https://github.com/shopsys/shopsys/releases/new`.
3. **Read the CHANGELOG.** Find the first existing `## v` heading. New content goes immediately above it. If no such heading exists, insert just after the line `<!-- Add generated changelog below this line -->` (the convention used by `CHANGELOG-X.Y.md` headers); if neither exists, prepend to the top.
4. **Build the inserted block.** A `## [<version>](https://github.com/shopsys/shopsys/compare/<previous-tag>...<version>) (YYYY-MM-DD)` heading (today's date) + a blank line + the generated markdown + a trailing blank line. The bracketed/linked heading form is required — `CheckChangelogForTodaysDateReleaseWorker` matches the regex `#\#\# \[<version>\]\(.*\) \(\d+-\d+-\d+\)#` and fails out of the release stage if the heading is a plain `## <version> (date)`.
5. **Write.** Update the CHANGELOG file. Do **not** run `git add` or commit — `UpdateChangelogReleaseWorker` runs `phing markdown-fix` and commits afterwards.
6. **Report.** Print:
   - Inserted heading.
   - First 3 lines of the generated body.
   - Byte count.
   - Path written.

On `--dry-run`, do steps 1-2 and print what *would* be inserted, then exit without writing.

## Failure handling

- **`gh api` error / non-2xx response** → leave the CHANGELOG untouched, print the response body, instruct the operator to open `https://github.com/shopsys/shopsys/releases/new?tag=<version>&target=<branch>` manually and paste themselves. The `/release` orchestrator should detect this exit code and fall back to a regular surface-the-prompt flow.
- **`gh` not authenticated** → tell the operator to run `gh auth login`. Do not attempt to authenticate for them.
- **Generated markdown is empty** (no PRs between tags) → write `_No changes since previous tag._` as the body and print a warning so the operator can double-check.

## Constraints

- Never commit, push, or tag.
- Never edit any file other than the resolved CHANGELOG path.
- Never post to GitHub on the operator's behalf — `releases/generate-notes` is a read-only generator endpoint; do not switch to `POST /releases` or any other write endpoint.
