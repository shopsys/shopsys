---
name: coding-conventions
description: Coding principles and conventions for the Shopsys monorepo — DRY/KISS/reuse, comment and docblock quality, test-code rules, the per-folder visibility/typing rules (packages vs project-base vs utils), and documentation best practices. Read it before writing or modifying PHP so your code matches the codebase.
---

# coding-conventions (monorepo)

The principles are authored from the project perspective and are the same here. **Read the canonical:**

- `project-base/.agents/skills/coding-conventions/SKILL.md`

Then apply the monorepo delta (`.agents/skills/monorepo-vs-project/SKILL.md`). The canonical's "Visibility & typing" section describes only the project rule (`final` / `private` / typehints). In the monorepo that rule applies to **`project-base/` and `utils/`** (incl. `utils/releaser` — internal tooling, not released downstream), but the monorepo adds a second rule for the framework source, which here is **editable**:

- **`packages/`** — classes are expected to be extended/overridden in project-base, so keep them easy to change:
  - use `protected` (not `private`);
  - do **not** use typehints/return types in entities and data objects (no parameter, return, or property types);
  - do **not** use `final` — except FormType classes, where `final` is required.

Everything else (DRY/KISS, comments, docblocks, `{@inheritdoc}`, `@inject` test rule) is identical to the canonical.
