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

# Create a CRUD controller for the administration

A CRUD controller gives an entity a complete admin UI — a datagrid list page and, once a
handler is registered, detail / create / edit / delete pages — from a single class with a
few `configure*()` methods. It replaces the hand-written "controller + grid factory + form
handling + flash messages + menu item + routes + role" boilerplate the older admin
controllers still carry.

**Use it** for any new admin section that manages one entity through its facade.
**Don't use it** for pages that are not a record list (dashboards, settings forms,
wizards) or where the edit page is a bespoke multi-tab workflow (e.g. the order detail) —
those stay plain `AdminBaseController` controllers.

Assumption: the entity already has the standard model layer — `Facade` (`getById`,
`create`, `edit`, `delete`), `Data` object + `DataFactory` (`create`, `createFrom<Entity>`)
and an admin `FormType`. If it doesn't, build that first (`shopsys-architecture` skill,
model architecture docs) — the CRUD controller only wires it together.

## Where to read more

Read the source first — the docblocks are complete and the code is short. Docs second.

| Topic | Source (read-only) | Docs |
|---|---|---|
| Base controller: `configure*()` hooks, actions, list-domain helpers | `vendor/shopsys/administration/src/Controller/AbstractCrudController.php` | [Configuration reference](https://docs.shopsys.com/en/latest/administration/crud-controller/reference/crud-controller/) |
| `CrudConfig` — every configurable option | `vendor/shopsys/administration/src/Component/Config/CrudConfig.php` | same page, section *CRUD Config* |
| Handler interfaces | `vendor/shopsys/administration/src/Component/Crud/Handler/` | [Handlers](https://docs.shopsys.com/en/latest/administration/crud-controller/reference/handlers/) |
| Hook interfaces (before/after/onError) | `vendor/shopsys/administration/src/Component/Crud/Extension/` | same page, section *Hooks System* |
| Form configurator (FormType vs builder) | `vendor/shopsys/administration/src/Component/Crud/Form/CrudFormConfigurator.php` | *configureForm* section of the configuration reference |
| Datagrid, fields, row actions | `vendor/shopsys/administration/src/Component/Datagrid/` | [Datagrid](https://docs.shopsys.com/en/latest/administration/datagrid/), [Fields](https://docs.shopsys.com/en/latest/administration/datagrid/fields/), [Row actions](https://docs.shopsys.com/en/latest/administration/datagrid/row-actions/) |
| Top actions (`Action`, `ActionsConfig`) | `vendor/shopsys/administration/src/Component/Action/`, `.../Component/Config/ActionsConfig.php` | [Actions](https://docs.shopsys.com/en/latest/administration/crud-controller/reference/actions/) |
| Extending an existing CRUD controller | `vendor/shopsys/administration/src/Controller/AbstractCrudControllerExtension.php` | [Extending](https://docs.shopsys.com/en/latest/administration/crud-controller/getting-started/extending-existing-crud-controller/) |
| Roles, `#[ForRole]`, `#[CanView]`… | `vendor/shopsys/framework/src/Component/Security/Attribute/` | [Admin rights](https://docs.shopsys.com/en/latest/administration/admin-rights/) |
| Menu sections and positioning | `vendor/shopsys/framework/src/Model/AdminNavigation/SideMenuBuilder.php` (constants) | [Administration menu](https://docs.shopsys.com/en/latest/administration/administration-menu/#positioning-menu-items) |
| Page templates you can override | `vendor/shopsys/administration/templates/crud/{list,detail,new,edit}.html.twig` | — |

Working examples shipped with the platform (`vendor/shopsys/administration/src/`):

- `Controller/TransportGroupController.php` + `Model/Transport/TransportGroupCrudHandler.php` — the minimal full CRUD: FormType, one column, drag-and-drop ordering, `#[ForRole]`, menu positioning.
- `Controller/BlogArticleAuthorController.php` — full CRUD plus a custom edit template with an extra grid (`getEditTemplate()` / `getEditViewData()`).
- `Controller/ProductReviewController.php` + `Model/ProductReview/ProductReviewEditHandler.php` — edit-only handler, `configureQuery()` with computed columns, templates per column, domain quick filter, `disable()` by feature flag.

## What you get and how it fits together

```
#[CrudController(Entity::class)]  Controller  ── configure(CrudConfig)         menu, names, route prefix, role section, handlers, domain control
                                              ── configureDatagrid(Datagrid)  list columns, ordering, drag&drop, row actions
                                              ── configureQuery(QueryBuilder) list DQL (root alias is `o`)
                                              ── configureForm(...)           create/edit form: useFormType() OR useBuilder()
                                              ── configureActions(...)        top buttons per page (ActionType::LIST/EDIT/…)
                                              ── getEditTemplate()/getEditViewData()  custom edit page
Handler (implements *HandlerInterface)        ── getById / createData / create / createDataFromEntity / edit / delete → delegates to the Facade
Entity implements Presentable                 ── toHumanReadable() used in titles, breadcrumbs, flash messages
Extension (#[CrudControllerExtension])        ── same configure*() methods + before/after/onError hooks, for controllers you don't own
```

- **Routes** are generated from the controller class name with the `Controller` / `CrudController`
  suffix stripped: `PriceListController` → `/admin/price-list/`, route names
  `admin_crud_price_list_{list|detail|create|edit|delete}` (`{entityId}` param). `setRoutePrefix()` adds a segment in front.
- **Role** `ROLE_CRUD_<CONTROLLER_NAME>` is generated and registered in the role matrix
  automatically. Put `#[ForRole(AdminRoleConstant::ROLE_X)]` on the class to reuse an
  existing role instead — then no own role is registered.
- **Menu item** and **page titles / breadcrumbs** are derived from the entity class name
  (singular/plural inflection, auto-registered translation keys). Override only when the
  inflection is wrong or the label should differ (`setEntityNameSingular/Plural`, `setMenuTitle`).
- **Actions are enabled by handlers.** Only List is on by default; registering a handler
  enables the actions its interface covers. `enableAction()` / `disableAction()` only work
  for actions that have a handler.
- **Default row actions** (edit, delete) and the **New** top button appear automatically
  when the matching action is enabled — you don't add them.

## Workflow

### 1. Controller

`app/src/Controller/Admin/<Entity>Controller.php`:

```php
#[CrudController(<Entity>::class)]
// #[ForRole(AdminRoleConstant::ROLE_…)]  ← only to reuse an existing role
final class <Entity>Controller extends AbstractCrudController
{
    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config
            ->setMenuSection(SideMenuBuilder::ROOT_…, SideMenuBuilder::SECTION_…, ['after' => SideMenuBuilder::LIST_…])
            ->registerHandler(<Entity>CrudHandler::class);
    }
}
```

Nothing else to register — `App\Controller\` is already a service resource in
`app/config/services.yaml`, and `AbstractCrudController` carries the
`shopsys.admin.crud_controllers` autoconfigure tag that the bundle's compiler passes and
route loader pick up.

`AbstractCrudController` receives its own dependencies through `#[Required]` setters, so
your constructor is free for your services (see `BlogArticleAuthorController`).

`CrudConfig` options (all fluent): `setEntityNameSingular/Plural`, `setMenuTitle`,
`setMenuSection(section, submenu, position)`, `setMenuIcon` (root-level items only),
`visibleInMenu`, `setRoutePrefix`, `setCustomRoleSection` (constants in
`AdminRoleSectionsProvider`; default = menu section), `registerHandler` / `unregisterHandler`,
`enableAction` / `disableAction`, `setListDomainControl`, `disable` (hide the whole controller,
e.g. behind a feature flag).

### 2. Handler

`app/src/Model/<Entity>/<Entity>CrudHandler.php` (or `…EditHandler`, `…DeleteHandler` when
narrower). Pick the narrowest interface from `Shopsys\AdministrationBundle\Component\Crud\Handler`:

| Interface | Adds | Enables |
|---|---|---|
| `ReadHandlerInterface` | `getById(int): Presentable` | detail |
| `DeleteHandlerInterface` | `delete(object)` | delete (+ detail) |
| `EditHandlerInterface` | `createDataFromEntity(object): object`, `edit(object, object)` | edit (+ detail) |
| `CreateHandlerInterface` | `createData(): object`, `create(object): Presentable` | create (+ detail) |
| `CrudHandlerInterface` | all of the above | everything |

Rules that bite:

- Never implement the bare `HandlerInterface` — it's only a marker for service discovery.
- `getById()` **must return a `Presentable`** — the entity has to implement
  `Shopsys\FrameworkBundle\Component\Utils\Presentable` (`toHumanReadable()`), otherwise the
  CRUD registry throws a `RuntimeException` the first time the controller is loaded. Let
  `getById()` throw the facade's not-found exception (they extend `NotFoundHttpException`, so
  the page is a 404).
- One handler per action; `CrudHandlerInterface` claims all actions, so it cannot be mixed
  with other handlers on the same controller. Replace a handler with
  `unregisterHandler()` + `registerHandler()`.
- Handlers are plain services. The `App\` resource glob in `app/config/services.yaml`
  matches class names ending in `Handler` — keep that suffix or register the service by hand.
- Start each method with `Assert::isInstanceOf($entity, <Entity>::class)` (see
  `TransportGroupCrudHandler`) — the interface is typed `object`.
- Handlers hold no business logic. Everything goes through the facade; a handler may only
  add access filtering (see `ProductReviewEditHandler::getById()`).

### 3. List page

```php
#[Override]
protected function configureDatagrid(Datagrid $datagrid): void
{
    $datagrid
        ->add('name', ['label' => t('Name')])
        ->add('domainId', ['label' => t('Domain')])            // only if $this->domain->isMultidomain()
        ->add('status', ['label' => t('Status'), 'virtual' => true, 'property' => 'computedStatus',
                         'template' => '@ShopsysAdministration/content/…/grid/status.html.twig']);
    $datagrid->setDefaultOrder('name', OrderingEnum::ASC);     // or enableDragAndDrop('position')
    $datagrid->actions()->add(RowAction::create('publish', t('Publish'), 'eye')->linkToRoute(…));
}

#[Override]
protected function configureQuery(QueryBuilder $queryBuilder): void
{
    // root entity alias is always `o`
    $queryBuilder->addSelect('CASE … END AS computedStatus')->andWhere('o.deleted = false');
}
```

Field options: `label`, `visible`, `sortable`, `virtual` (not selected from the entity —
pair with `property` or `transform`), `property` (DQL path, e.g. `product.id`), `template`,
`transform`, `help`. Datagrid methods: `add` / `update` / `remove` / `reorder`,
`setDefaultOrder`, `setPagination`, `enableDragAndDrop(field)`, `actions()` (row actions:
`add` / `update` / `delete` / `reorder`).

**Multi-domain lists**: `setListDomainControl(CrudListDomainControl::QUICK_FILTER|SWITCHER, $allowedDomainIds)`
in `configure()`. For entities implementing `DomainSeparatedEntityInterface` the domain
condition is applied for you; otherwise use `addListDomainIdsCondition($qb, 'x.domainId')`,
`getEffectiveListDomainIds()` or `getSelectedListDomainId()` in `configureQuery()` — the
configuration reference has the join-vs-subselect guidance.

### 4. Form

```php
#[Override]
protected function configureForm(CrudFormConfigurator $formConfigurator, ?object $entity = null): void
{
    $formConfigurator->useFormType(<Entity>FormType::class, ['<entity>' => $entity]);   // $entity is null on create
}
```

`useFormType()` and `useBuilder()` are mutually exclusive (`CrudFormAlreadyConfiguredException`).
Prefer a FormType — it's reusable, testable and the option name (`'brand' => $entity`)
follows the existing admin FormTypes. `useBuilder()` is for tiny forms and is the only mode
extensions can add fields to. `setFormOption()` must be called before `useBuilder()`.

### 5. Top actions, custom routes, templates (optional)

- `configureActions(ActionsConfig $actions)`: `$actions->add(ActionType::EDIT, Action::create('name', t('Label'))->linkToRoute(...)->displayIf(...))`,
  `update()`, `remove()`. `linkToRoute` / `displayIf` closures receive the edited entity on
  EDIT and `null` elsewhere. `linkToCrud(OtherController::class, ActionType::LIST)` links between CRUDs.
- Custom endpoints are ordinary `#[Route]` methods on the same controller; guard them with
  `#[CanView]` / `#[CanEdit]` / `#[CanDelete]` (no role needed — they fall back to the
  controller's role) and `#[CsrfProtection]` for state-changing GETs (the action link then
  carries the token automatically).
- Edit page with extra content: override `getEditTemplate()` (extend
  `@ShopsysAdministration/crud/edit.html.twig`) and `getEditViewData()`.

### 6. Extending a CRUD controller you don't own

`app/src/Controller/Admin/<Entity>ControllerExtension.php`:

```php
#[CrudControllerExtension(crudController: \Shopsys\AdministrationBundle\Controller\<Entity>Controller::class)]
final class <Entity>ControllerExtension extends AbstractCrudControllerExtension implements CrudEditHookExtensionInterface
{
    public function configureDatagrid(Datagrid $datagrid): void { $datagrid->remove('…')->add('…', […]); }
    public function beforeEdit(object $entity, object $data): void {}
    public function afterEdit(object $entity, object $data): void {}
    public function onEditError(object $entity, object $data, Throwable $exception): void {}
}
```

- Same `configure*()` methods as the controller; runs after the original (and after other
  extensions in `priority` order — `App\` extensions without a priority run last).
- Hooks: `CrudCreate|Edit|DeleteHookExtensionInterface` — run inside the same DB transaction;
  an exception in a hook rolls everything back. Add your own flash message to replace the
  default one; call `addErrorFlash()` in `on*Error` for a custom error text.
- `#[ForRole]` on the extension overrides the extended controller's role.
- Don't extend the controller class itself and don't re-register it — extensions are the
  supported mechanism, and the admin CRUD controllers in `vendor/` are not `final` only
  because of the framework's own conventions.

## Verify

1. Clear cache and open `/admin/<controller-name>/` — the item appears in the configured menu
   section; open create, edit, detail, delete.
2. New translation keys (`t()` in labels, `toHumanReadable()`, entity names): `php phing translations-dump`
   in the php-fpm container and fill the `.po` files (`shopsys-commands` skill).
3. Tests (`test-writing` skill): the HTTP smoke test discovers the new routes on its own —
   `{entityId}` gets `1`, `_delete` routes get a CSRF token and expect a 302. Make sure demo
   data contains the entity with id 1 or add a `customizeByRouteName()` entry in
   `app/tests/App/Smoke/Http/RouteConfigCustomization.php`. Cover the handler's
   non-trivial logic (filters, access rules) with a functional test; the facade is tested on
   its own.
4. `php phing standards-fix` and PHPStan; a new `#[ForRole]`-less controller adds a
   `ROLE_CRUD_*` row to the administrator role matrix — check the role settings page renders.

## Checklist

- [ ] Entity implements `Presentable`; `toHumanReadable()` never throws and includes an identifier.
- [ ] Controller has `#[CrudController(Entity::class)]`, extends `AbstractCrudController`, lives in `app/src/Controller/Admin/`.
- [ ] Handler implements the narrowest sufficient interface, class name ends with `Handler`, asserts types, delegates to the facade.
- [ ] `configure()`: menu section (+ position), handler registered, role section / `#[ForRole]` decided consciously.
- [ ] Datagrid columns labelled with `t()`; default order or drag-and-drop set; domain column only in multidomain.
- [ ] Form via an existing/new admin `FormType` (builder only for trivial forms).
- [ ] Smoke test passes for all five routes; translations dumped; standards + PHPStan green.
