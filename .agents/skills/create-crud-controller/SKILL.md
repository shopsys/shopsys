---
name: create-crud-controller
description: >
  Create or extend an administration CRUD controller (list / detail / create / edit / delete
  pages for an entity) built on Shopsys\AdministrationBundle\Controller\AbstractCrudController.
  Use when the user asks for a new admin section, admin list, admin management page or
  admin CRUD for an entity, or wants to change an existing CRUD controller (columns, form,
  actions, hooks, role). Explains the pieces (controller, handler, form, datagrid, actions,
  extension) and where each is documented and implemented.
user_invocable: true
version: 1.0.0
---

# create-crud-controller (monorepo)

The canonical instructions for this skill are authored from the project perspective. **Read them first:**

- `project-base/.agents/skills/create-crud-controller/SKILL.md`

Then apply the monorepo delta (package-first + path/role remap):

- `.agents/skills/monorepo-vs-project/SKILL.md`

The method — controller → handler → datagrid → form → actions, the handler rules, the
extension/hook mechanism, verification and the checklist — follows the canonical unchanged.
What differs here is *where* the classes go, *which conventions* they follow, and that you
are usually also the author of the docs and upgrade notes.

## Where the code goes (package-first)

A CRUD controller in the monorepo is framework code that every downstream project receives, so it lives in the administration bundle, not in `project-base/`:

| Piece | Monorepo location | Namespace |
|---|---|---|
| Controller | `packages/administration/src/Controller/<Entity>Controller.php` | `Shopsys\AdministrationBundle\Controller` |
| Handler | `packages/administration/src/Model/<Area>/<Entity>CrudHandler.php` | `Shopsys\AdministrationBundle\Model\<Area>` |
| Entity, Facade, Data, DataFactory | `packages/framework/src/Model/<Area>/` (existing model layer) | `Shopsys\FrameworkBundle\Model\<Area>` |
| Admin FormType | `packages/framework/src/Form/Admin/<Area>/<Entity>FormType.php` | `Shopsys\FrameworkBundle\Form\Admin\<Area>` |
| Column / edit templates | `packages/administration/templates/content/<entity>/…` (`@ShopsysAdministration/content/<entity>/…`) | — |
| Menu section constants | `packages/framework/src/Model/AdminNavigation/SideMenuBuilder.php` | — |
| Existing role constants | `packages/framework/src/Component/Security/Role/AdminRoleConstant.php` | — |

`project-base/app/src/Controller/Admin/` is only for the rare project-specific case — typically a `#[CrudControllerExtension]` demonstrating how a project customises a bundle controller. The canonical's `vendor/shopsys/administration/src/…` and `vendor/shopsys/framework/src/…` paths are `packages/administration/src/…` and `packages/framework/src/…` here, and they are editable.

Service registration needs nothing from you: `packages/administration/config/services.yaml` registers everything under `src/` (handlers included, regardless of the class-name suffix) and `src/Controller/` with `controller.service_arguments`. Keep the `…Handler` suffix anyway — downstream projects rely on it for their own service glob.

## Conventions that replace the canonical's project rules

Package code follows `.agents/skills/coding-conventions/SKILL.md`, which inverts the canonical's `final` / `private` / full-typehint rules:

- Controller and handler classes are **not `final`**, constructor properties and helper methods are **`protected`** (`protected readonly Facade $facade`), so projects can extend them. The FormType stays `final` (Symfony requirement) — projects override it via `getExtendedTypes()` or the form-extension mechanism, and its option name follows the existing admin FormTypes (`'transportGroup' => $entity`).
- Mark every overridden `configure*()`, `getEditTemplate()`, `getEditViewData()` and handler method with `#[Override]`; handler methods carry a `{@inheritdoc}` docblock plus a *why* line when the method does more than delegate (see `ProductReviewEditHandler`).
- `Assert::isInstanceOf()` at the start of every handler method that receives `object` — the three shipped handlers are the pattern to copy.

## Docs, translations, upgrade notes — you own them here

- **Documentation is local** — `docs/administration/crud-controller/` (getting-started + reference), `docs/administration/datagrid/`, `docs/administration/admin-rights.md`, `docs/administration/administration-menu.md`. Search it with `.agents/skills/docs-researcher/SKILL.md` instead of docs.shopsys.com. When you change the CRUD component itself (`AbstractCrudController`, `CrudConfig`, handler or hook interfaces, `Datagrid`), update the matching reference page in the same PR.
- **Translations**: `php phing translations-dump` (php-fpm container, `.agents/skills/shopsys-commands/SKILL.md`) writes the new `t()` keys and the auto-generated entity names into `packages/*/translations/*.po` — those `.po` changes are part of the package and belong in the commit (commit message conventions in `.agents/skills/commit-conventions/SKILL.md`).
- **Upgrade notes** (`upgrade-notes/_template.md`, `/generate-upgrade-notes`) whenever a project has to react: a new CRUD controller that replaces a legacy `packages/framework/src/Controller/Admin/*Controller.php` (list the removed routes, templates and menu items; keep the old `AdminRoleConstant` role via `#[ForRole]` so administrator permissions survive), a new `ROLE_CRUD_*` role, or any signature change in the CRUD component. Note the `{pullRequestId}` placeholder is backfilled by `/adhoc-pr`.

## Tests

- Unit tests for CRUD-component code go to `packages/administration/tests/Unit/…` and run with `--configuration packages/administration/phpunit.xml` (`.agents/skills/test-writing/SKILL.md`).
- The HTTP smoke test and functional tests still live in `project-base/app/tests/` — route customisation is `project-base/app/tests/App/Smoke/Http/RouteConfigCustomization.php`, and the entity with id `1` the smoke test requests comes from `project-base/app/src/DataFixtures/Demo/`.
