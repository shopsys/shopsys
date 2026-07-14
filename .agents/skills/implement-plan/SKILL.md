---
name: implement-plan
description: Implements an approved plan from docs/plans and tracks phase completion.
---

# implement-plan (monorepo)

The canonical instructions are authored from the project perspective. **Read them:**

- `project-base/.agents/skills/implement-plan/SKILL.md`

This skill is process-only, so **no `monorepo-vs-project` path/architecture delta applies** — `docs/plans/` and the verification commands are the same here. The one thing to carry over from `.agents/skills/monorepo-vs-project/SKILL.md`: where the plan you are implementing places new business logic, follow **package-first** (it belongs in `packages/`, not `project-base/`).
