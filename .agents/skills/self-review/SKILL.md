---
name: self-review
description: Fresh-eyes review of your OWN finished change before you hand it off or commit it — checks design, simplicity, naming, tests and delivery (upgrade notes, commit messages) against the project conventions, ideally with parallel read-only subagents, then fixes what is real. Use after implementing a plan or change and before reporting it done.
---

# self-review (monorepo)

The method is authored from the project perspective. **Read the canonical:**

- `project-base/.agents/skills/self-review/SKILL.md`

Then apply the monorepo delta (`.agents/skills/monorepo-vs-project/SKILL.md`). For this skill that means:

- **Package-first applies to the review too:** a new class in `project-base/` that holds reusable business logic is a finding — it belongs in `packages/`. In the *Design & data model / API* dimension also check that `packages/` code follows the editable-framework rules (`protected`, no typehints in entities and data objects, no `final`) and `project-base/` and `utils/` follow the project rules (`.agents/skills/coding-conventions/SKILL.md`).
- **Scope** is the diff against the default branch, (resolve it with `gh repo view --json defaultBranchRef`, don't hardcode it); upgrade notes live in `upgrade-notes/` and a storefront + backend change gets both notes.
- **Verification commands** are the monorepo ones in `.agents/skills/shopsys-commands/SKILL.md` — in particular coding standards through `php phing standards-fix` (not `ecs check` on single files) and PHPStan with the root `phpstan.neon`.
