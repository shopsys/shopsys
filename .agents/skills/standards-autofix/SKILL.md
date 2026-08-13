---
name: standards-autofix
description: >
  Finishes a failed coding-standards check: the pipeline has already run `phing standards-fix`,
  so this fixes by hand what the fixer could not (phpstan, phplint, twig-lint, leftover ecs),
  then packages everything as fixup! commits targeting the PR commits that introduced each
  violation. Used by CI when the "Check standards" job fails on a pull request; can also be run
  locally on a branch. It never rewrites existing history — it only appends commits for the
  author to review and autosquash.
user_invocable: true
version: 1.0.0
---

# standards-autofix (monorepo)

The canonical instructions for this skill are authored from the project perspective. **Read them first:**

- `project-base/.agents/skills/standards-autofix/SKILL.md`

Then apply the monorepo delta (package-first + path/role remap):

- `.agents/skills/monorepo-vs-project/SKILL.md`

The method — mindset, the read-the-log → fix → fixup-commits flow, author attribution, the narrow command allowlist, and the rules — follows the canonical unchanged. What differs here is the log you read, the conventions the fixes follow, and the reporting.

## Environment delta (GitHub Actions instead of GitLab CI)

You run in the `ai-standards-fix` job of the Docker build workflow, after the `standards` job failed. The job runs no containers at all: it checks the branch out, downloads that job's log, and hands it to you. Editing files is the entire mechanism.

- **Target:** `$ARGUMENTS` carries the same `--merge-base` and `--standards-log` flags the canonical describes; there is no PR reference and no container name. Empty still means a local run on the current branch.
- **Log content:** `--standards-log` is the raw GitHub Actions log of the *Check standards* job, so every line is prefixed with the job name, step name and a timestamp. The failure is `php phing standards` or `project-base/app/check-schema.sh`; the `ux:icons:lock` check lives in a different job here, so you will never see it.
- **Paths are repo-relative** exactly as the log prints them — `packages/framework/src/...`, `project-base/app/src/...`. There is no `app/` prefix strip.
- **Conventions:** fixes in `packages/` follow the monorepo package rules (`protected`, no `final` except FormType, annotation docblocks); `project-base/` and `utils/` use `final`/`private`/full typehints — see `.agents/skills/coding-conventions/SKILL.md`.
- **Schema drift** (`check-schema.sh`) cannot be fixed here — regenerating the schema needs the application. Report it under "left for a human".

## Reporting delta

Nothing is posted to the pull request. Your summary goes to the CI job log exactly as the canonical describes, and you have no `gh` tool — the commits themselves are the deliverable a reviewer sees.

Two things to mention in it that are specific to this workflow:

- The **"Block fixup commits" check will go red** and stays red until the author squashes the fixups. That is intentional, not a failure of your work.
- The push uses a personal access token rather than `GITHUB_TOKEN`, so your commits **do** trigger a fresh CI run — that run is what actually verifies the fix.
