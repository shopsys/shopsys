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
| Base controller: `configure*()` hooks, actions | `vendor/shopsys/administration/src/Controller/AbstractCrudController.php` | [Configuration reference](https://docs.shopsys.com/en/latest/administration/crud-controller/reference/crud-controller/) |
| `CrudConfig` — every configurable option | `vendor/shopsys/administration/src/Component/Config/CrudConfig.php` | same page, section *CRUD Config* |
| Handler interfaces | `vendor/shopsys/administration/src/Component/Crud/Handler/` | [Handlers](https://docs.shopsys.com/en/latest/administration/crud-controller/reference/handlers/) |
| Hook interfaces (before/after/onError) | `vendor/shopsys/administration/src/Component/Crud/Extension/` | same page, section *Hooks System* |
| Form configurator (FormType vs builder) | `vendor/shopsys/administration/src/Component/Crud/Form/CrudFormConfigurator.php` | *configureForm* section of the configuration reference |
| Datagrid, fields, row actions | `vendor/shopsys/administration/src/Component/Datagrid/` | [Datagrid](https://docs.shopsys.com/en/latest/administration/datagrid/), [Fields](https://docs.shopsys.com/en/latest/administration/datagrid/fields/), [Row actions](https://docs.shopsys.com/en/latest/administration/datagrid/row-actions/) |
| Domain control, quick search, the condition tree | `vendor/shopsys/administration/src/Component/Datagrid/Condition/`, `.../Datagrid/DomainControl/` | [Narrowing the records](https://docs.shopsys.com/en/latest/administration/datagrid/narrowing/) |
| Filters: built-in types, writing your own | `vendor/shopsys/administration/src/Component/Datagrid/Filter/` | [Filters](https://docs.shopsys.com/en/latest/administration/datagrid/filters/) |
| Top actions (`Action`, `ActionsConfig`) | `vendor/shopsys/administration/src/Component/Action/`, `.../Component/Config/ActionsConfig.php` | [Actions](https://docs.shopsys.com/en/latest/administration/crud-controller/reference/actions/) |
| Extending an existing CRUD controller | `vendor/shopsys/administration/src/Controller/AbstractCrudControllerExtension.php` | [Extending](https://docs.shopsys.com/en/latest/administration/crud-controller/getting-started/extending-existing-crud-controller/) |
| Roles, `#[ForRole]`, `#[CanView]`… | `vendor/shopsys/framework/src/Component/Security/Attribute/` | [Admin rights](https://docs.shopsys.com/en/latest/administration/admin-rights/) |
| Menu sections and positioning | `vendor/shopsys/framework/src/Model/AdminNavigation/SideMenuBuilder.php` (constants) | [Administration menu](https://docs.shopsys.com/en/latest/administration/administration-menu/#positioning-menu-items) |
| Page templates you can override | `vendor/shopsys/administration/templates/crud/{list,detail,new,edit}.html.twig` | [`setTemplate()`](https://docs.shopsys.com/en/latest/administration/crud-controller/reference/crud-controller/#settemplateactiontype-actiontype-string-template) and [`configureTemplateParameters()`](https://docs.shopsys.com/en/latest/administration/crud-controller/reference/crud-controller/#template-parameters) sections of the configuration reference |

Working examples shipped with the platform (`vendor/shopsys/administration/src/`):

- `Controller/TransportGroupController.php` + `Model/Transport/TransportGroupCrudHandler.php` — the minimal full CRUD: FormType, one column, drag-and-drop ordering, `#[ForRole]`, menu positioning.
- `Controller/BlogArticleAuthorController.php` — full CRUD plus a custom edit template with an extra grid (`setTemplate(ActionType::EDIT, …)` / `configureTemplateParameters()`).
- `Controller/ProductReviewController.php` + `Model/ProductReview/ProductReviewEditHandler.php` — edit-only handler, `configureQuery()` with computed columns, templates per column, domain filter, searchable fields, filters (`ChoiceFilter`, `ProductFilter`, `BooleanFilter`), `disable()` by feature flag.

## What you get and how it fits together

```
#[CrudController(Entity::class)]  Controller  ── configure(CrudConfig)         menu, names, route prefix, role section, handlers, domain control
                                              ── configureDatagrid(Datagrid)  list columns, ordering, drag&drop, row actions, searchable fields, filters()
                                              ── configureQuery(QueryBuilder) list DQL (root alias is `o`), fixed conditions
                                              ── configureForm(...)           create/edit form: useFormType() OR useBuilder()
                                              ── configureActions(...)        top buttons per page (ActionType::LIST/EDIT/…)
                                              ── configureTemplateParameters(...)  extra variables for a template replaced via $config->setTemplate()
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
- **The actions themselves are `final`** (`listAction()`, `detailAction()`, `createAction()`,
  `editAction()`, `deleteAction()`). Everything is customised through the `configure*()`
  hooks, `setTemplate()` and the extension hooks; a flow they cannot express gets its own
  `#[Route]` action after `disableAction()` of the built-in one.

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
| `DeleteHandlerInterface` | `delete(Presentable)` | delete (+ detail) |
| `EditHandlerInterface` | `createDataFromEntity(Presentable): object`, `edit(Presentable, object)` | edit (+ detail) |
| `CreateHandlerInterface` | `createData(): object`, `create(object): Presentable` | create (+ detail) |
| `CrudHandlerInterface` | all of the above | everything |

Rules that bite:

- Never implement the bare `HandlerInterface` — it's only a marker for service discovery.
- `getById()` and `create()` declare the **concrete entity** as their return type
  (`getById(int $id): <Entity>`, see `ProductReviewEditHandler`), not the interface's `Presentable`.
  The entity has to implement `Shopsys\FrameworkBundle\Component\Utils\Presentable`
  (`toHumanReadable()`), otherwise the CRUD registry throws a `RuntimeException` the first time
  the controller is loaded. Let
  `getById()` throw the facade's not-found exception (they extend `NotFoundHttpException`, so
  the page is a 404).
- One handler per action; `CrudHandlerInterface` claims all actions, so it cannot be mixed
  with other handlers on the same controller. Replace a handler with
  `unregisterHandler()` + `registerHandler()`.
- Handlers are plain services. The `App\` resource glob in `app/config/services.yaml`
  matches class names ending in `Handler` — keep that suffix or register the service by hand.
- Start each method that receives the entity or the data object with
  `Assert::isInstanceOf($entity, <Entity>::class)` (see `TransportGroupCrudHandler`) — the
  interface types parameters as `Presentable` / `object` and PHP does not allow narrowing them.
- Handlers hold no business logic. Everything goes through the facade; a handler may only
  add access filtering (see `ProductReviewEditHandler::getById()`).

### 3. List page

```php
#[Override]
protected function configureDatagrid(Datagrid $datagrid): void
{
    $datagrid
        ->add('name', ['label' => t('Name'), 'searchable' => true])   // joins the quick search
        ->add('createdAt', ['label' => t('Created'), 'filterable' => true])  // filter picked by the path (DateFilter)
        ->add('status', ['label' => t('Status'), 'virtual' => true, 'property' => 'computedStatus',
                         'template' => '@ShopsysAdministration/content/…/grid/status.html.twig']);
    // the domain column of a list working with several domains is added automatically

    $datagrid->filters()
        ->add(ChoiceFilter::new('status', t('Status'))->setChoices($this->statusEnum->getAllIndexedByTranslations()))
        ->add(EntityFilter::new('unit', t('Unit')));

    $datagrid->setDefaultOrder('name', SortDirection::Ascending);     // or enableDragAndDrop('position')
    $datagrid->actions()->add(RowAction::create('publish', t('Publish'), 'eye')->linkToRoute(…));
}

#[Override]
protected function configureQuery(QueryBuilder $queryBuilder): void
{
    // root entity alias is always `o`
    $queryBuilder->addSelect('CASE … END AS computedStatus');
    // a fixed condition, the administrator never sees it
    $queryBuilder->andWhere('o.deleted = :deleted')->setParameter('deleted', false);
}
```

Field options: `label`, `visible`, `sortable`, `searchable` (text paths only; a combined field is
searched in its text paths), `filterable` (filter picked by the path; not for combined or
virtual fields), `virtual` (not selected from the entity — pair with `property` or
`transform`), `property` (DQL path, e.g. `product.id`, or an array of
paths — all are fetched, the template gets them as `value.<path>`, the column sorts by all of
them and without a template they are joined with a space; prefer this over hidden helper
fields), `template`, `transform`, `help`, `role` / `permission` (show the column only to
administrators with the permission on the role; role defaults to the controller role,
permission to `Permission::VIEW`; the data is not fetched, searched or filtered for others), `class` (CSS class of the
header and cells, e.g. `text-end`). Datagrid methods: `add` / `update` / `remove` / `reorder`,
`setDefaultOrder`, `setPagination`, `enableDragAndDrop(field)`, `actions()` (row actions:
`add` / `update` / `delete` / `reorder`), `filters()` (`add` / `remove`),
`getDomainControlScope()` (known while configuring), `getNarrowing()` (the quick search, the filter
panel and their condition, resolved once the datagrid is configured — fields and filters cannot change after that).

**Narrowing the list** — pick by who drives the condition:

- `'searchable' => true` on a field — the administrator types a text, it is looked for in
  every searchable field (diacritics-insensitive). Only paths leading to text.
- `'filterable' => true` on a field — the datagrid picks the filter by the value of the path
  (text, number, date, boolean, product; money, another entity and virtual fields have none —
  declare an `EntityFilter` for a small table, its select loads every related entity).
- `$datagrid->filters()->add(TextFilter::new('author.fullName', t('Author')))` — the
  administrator composes rules from the declared filters (AND/OR groups, operators per type).
  Always takes precedence: declared under the name of a `filterable` field it replaces the
  picked filter (`customerUser.email` field → filter name `customerUser_email`).
  Filters are offered sorted by label in the administration language.
  Built-in: `TextFilter`, `NumericFilter`, `DateFilter`, `BooleanFilter`, `ChoiceFilter`,
  `EntityFilter`, `ProductFilter`; a filter over another kind of value (`NumericFilter` over money)
  is refused when the datagrid is built; own type = extend `AbstractFilter`, see the Filters docs.
  A filter outranks the quick search: when filter rules are submitted, the search text is ignored.
  Over a collection "is not" / "is none of" negate the whole condition, a missing value is "not X".
- a fixed condition nobody switches off (`o.deleted = :deleted`) goes to `configureQuery()`
  as a parameterized `andWhere()`, next to the computed columns and joins of the list.

Both the quick search and the filters are switched by tabs above the datagrid; when the list
also has a domain filter, it moves to a select on the right of them.

**Multi-domain lists**: `setListDomainControl(DomainControlType::FILTER|SWITCHER|NONE, $allowedDomainIds, $filterNamespace, $domainIdPath)`
in `configure()`. For entities implementing `DomainSeparatedEntityInterface` the domain
condition is applied for you (`FILTER` is the default, the tabs/select show only when more
than one domain is allowed); an entity related to a domain in another way passes the path to
its domain (`domainIdPath: 'domains.domainId'`), other entities get no domain control. A list working with several domains (the domain filter
offering more than one) gets a `domainId` column automatically; add the field yourself only to change
its label or position.

### 4. Form

```php
#[Override]
protected function configureForm(CrudFormConfigurator $formConfigurator, ?Presentable $entity = null): void
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
- Page with extra content (any of list / detail / create / edit): `$config->setTemplate(ActionType::<PAGE>, '…')`
  in `configure()` — the template extends the default one (`@ShopsysAdministration/crud/edit.html.twig`)
  and only overrides the block where the extra content goes (`before_form`, `form`, `after_form`, `entity_log`
  on create/edit, `datagrid_controls`, `before_grid`, `grid`, `after_grid` on list — no `parent()` needed). The edit
  page gets the record as `entity` and shows the change history of `#[Loggable]` entities on its own. Hand it extra variables from `configureTemplateParameters(CrudTemplateParameters $templateParameters)`:
  the context of the rendered action is on `$templateParameters` — check `$templateParameters->isAction(ActionType::EDIT)`
  (or `hasEntity()` for every page showing a record), get the entity with `$templateParameters->getEntity(<Entity>::class)`
  (fails fast when missing or of another class, PHPStan knows the type) and call `$templateParameters->set('name', $value)`.
  See `BlogArticleAuthorController`.

### 6. Extending a CRUD controller you don't own

`app/src/Controller/Admin/<Entity>ControllerExtension.php`:

```php
#[CrudControllerExtension(crudController: \Shopsys\AdministrationBundle\Controller\<Entity>Controller::class)]
final class <Entity>ControllerExtension extends AbstractCrudControllerExtension implements CrudEditHookExtensionInterface
{
    public function configureDatagrid(Datagrid $datagrid): void { $datagrid->remove('…')->add('…', […]); }
    public function beforeEdit(Presentable $entity, object $data): void {}
    public function afterEdit(Presentable $entity, object $data): void {}
    public function onEditError(Presentable $entity, object $data, Throwable $exception): void {}
}
```

- Same `configure*()` methods as the controller; runs after the original (and after other
  extensions in `priority` order — `App\` extensions without a priority run last).
- Hooks: `CrudCreate|Edit|DeleteHookExtensionInterface` — run inside the same DB transaction;
  an exception in a hook rolls everything back. Add your own flash message to replace the
  default one; call `addErrorFlash()` in `on*Error` for a custom error text.
- Templates: `setTemplate()` in `configure()` and `configureTemplateParameters()` work the same
  as in the controller; the extension runs after it, so it can read the controller's variables
  with `has()` / `get()`. `setTemplate()` replaces the template the controller configured —
  extend that one (not `@ShopsysAdministration/crud/…`), or the controller's extra content disappears.
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
   non-trivial logic (access rules) and every declared filter with a functional test — request
   the list with the filter in the URL (`<gridId>_filter[...]`, see `FilterListTest`) and assert
   the rows; an own filter type gets a unit test on top (resolve → `buildCondition()`).
4. `php phing standards-fix` and PHPStan; a new `#[ForRole]`-less controller adds a
   `ROLE_CRUD_*` row to the administrator role matrix — check the role settings page renders.

## Checklist

- [ ] Entity implements `Presentable`; `toHumanReadable()` never throws and includes an identifier.
- [ ] Controller has `#[CrudController(Entity::class)]`, extends `AbstractCrudController`, lives in `app/src/Controller/Admin/`.
- [ ] Handler implements the narrowest sufficient interface, class name ends with `Handler`, asserts types, delegates to the facade.
- [ ] `configure()`: menu section (+ position), handler registered, role section / `#[ForRole]` decided consciously.
- [ ] Datagrid columns labelled with `t()`; default order or drag-and-drop set; no own domain column (it is added automatically).
- [ ] Searchable fields marked, filters declared for what the administrator narrows by, fixed conditions as parameterized `andWhere()` in `configureQuery()`.
- [ ] Form via an existing/new admin `FormType` (builder only for trivial forms).
- [ ] Smoke test passes for all five routes; translations dumped; standards + PHPStan green.
