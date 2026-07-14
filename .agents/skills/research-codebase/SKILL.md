---
name: research-codebase
description: Conducts comprehensive codebase research using parallel specialized subagents.
---

# research-codebase (monorepo)

The canonical instructions for this skill are authored from the project perspective. **Read them first:**

- `project-base/.agents/skills/research-codebase/SKILL.md`

Then apply the monorepo delta (package-first + path/role remap):

- `.agents/skills/monorepo-vs-project/SKILL.md`

In short, for *this* skill the delta means: in the research-document template, the layer sections become **Framework Layer (`packages/framework/`)** primary and **Project Layer (`project-base/app/`)** secondary; the `repository:` frontmatter/field is `shopsys/shopsys`; and every path the canonical writes as `app/…`, `storefront/…`, or `vendor/shopsys/<pkg>/src/…` is remapped to `project-base/app/…`, `project-base/storefront/…`, and the editable `packages/<pkg>/src/…` respectively. Everything else — the numbered workflow, parallel-subagent guidance, and metadata steps — follows the canonical unchanged.
