# Admin CRUD Controller Migration Implementation Plan

## Overview

Rewrite 9 legacy admin controllers from `packages/framework/src/Controller/Admin/` to the CRUD controller
(`Shopsys\AdministrationBundle\Controller\AbstractCrudController`) **using only what the CRUD controller offers today** —
no changes to `AbstractCrudController`. Each controller becomes one thin class in
`packages/administration/src/Controller/` plus one handler in `packages/administration/src/Model/…`, and its
grid factory, inline grid code and five Twig templates are deleted.

Delivered as **one pull request, one commit per controller** (plus one commit for upgrade notes).

## Implementation status (2026-09-11)

- [x] Phase 1 implemented: ParameterGroup, Navigation, NotificationBar, Slider, Advert, SalesRepresentative
- [x] Phase 2 implemented: ClosedDay, Store, PriceList
- [x] Phase 3 implemented: upgrade note `upgrade-notes/backend_20260911_191253.md` (PR number placeholder), docs section in `creating-a-new-crud-controller.md`; `phpstan-dead-code.neon` had no entries for the removed classes
- [ ] Verification (smoke tests, PHPStan, translation dump, manual checks) not run yet — see the success criteria of each phase

Deviations from the plan discovered during implementation:
- every converted entity had to implement `Presentable` (`toHumanReadable()`), the handler contract requires it
- CRUD menu items are named by their list route (`admin_crud_store_list`), so `ClosedDay` uses the default `last` position instead of `['after' => LIST_STORE]` (it was the last item of the Lists section anyway)
- computed columns must not combine `property` with `transform` (the grid reads the `property` key, the transform writes the field name); computed values use `virtual` + `transform` reading from `$row`
- `DefaultStoreCannotBeDeletedException` was added to the framework so the Store handler can refuse deleting the default store

## Current State Analysis

- 84 admin controllers exist; 4 already use the CRUD controller (`TransportGroupController`,
  `BlogArticleAuthorController`, `AdditionalServiceController`, `ProductReviewController`). All four were written
  as new features, so **no precedent exists for retiring a legacy controller** — this plan defines that pattern.
- The legacy controllers repeat the same 150–250 lines: Grid built from a QueryBuilder, four routes with
  `#[Route]`, `BreadcrumbOverrider`, flash messages, and a `list/listGrid/new/edit/detail`
  template quintet under `packages/administration/templates/content/<name>/`.
- Every candidate FormType hardcodes `back_route` to the old list route inside its `ActionBarType` options.
- `SideMenuBuilder` hardcodes each controller's list/new/edit menu items and route names; CRUD controllers
  register their own menu item via `setMenuSection()` (`CrudMenuSubscriber`).
- Smoke tests cover all admin routes generically; a few routes have entries in
  `project-base/app/tests/App/Smoke/Http/RouteConfigCustomization.php` (regex `admin_(stock|store)_setdefault`,
  regex `admin_.*_deleteconfirm`).
- No project-base overrides and no Cypress/functional tests touch any of the 9 admin pages.

### Scope decisions (agreed 2026-09-11)

| Decision | Outcome |
|---|---|
| Controllers with quick/advanced search | **Excluded** (BlogArticle, GiftVoucher, Inquiry, Newsletter, PromoCode, UploadedFile, TransferIssue). Branch `mg/crud-search` adds search to CRUD lists; revisit after merge. |
| Inline-edit controllers (Unit, Currency, OrderStatus, PricingGroup, ComplaintStatus) | **Excluded** — would lose in-grid editing and need a "delete and replace" dialog. |
| Transport, Payment | **Excluded** — lists are embedded in the shared transport-and-payment page. |
| Controllers needing new CRUD hooks | **Excluded** — see table below. The CRUD controller is not modified in this plan. |
| Route BC | Use `setRoutePrefix()` to keep the URL **base**; route **names** change to `admin_crud_*` and leaf segments change (`list/` → `/`, `new/` → `/create`). Documented in upgrade notes. |
| PR granularity | One PR, one commit per controller. |

#### Excluded because they need a CRUD hook that does not exist yet

| Controller | Missing hook | Why |
|---|---|---|
| Brand | `getListTemplate()` | list page renders a card with storefront brand-list URLs per domain |
| GiftPlan | `getListTemplate()` | gift-price settings form is embedded in the list page |
| Stock | `getListTemplate()` + `getListViewData()` | settings form embedded in list; "default (domain)" badges need `getDefaultDomainIdsIndexedByStockId()` |
| Flag | forbidden-action flash from handler | create refuses when not all domains are enabled; delete refuses on dependencies with a specific message |
| SeoPage | forbidden-action flash from handler | all-domains guard on create; `DefaultSeoPageCannotBeDeletedException` |
| Country | forbidden-action flash from handler | all-domains guard on create |
| Parameter | forbidden-action flash + post-edit redirect | all-domains guard on create; edit redirects to slider values (`editAction` is not hookable) |
| CustomerUserRoleGroup | forbidden-action flash + superadmin-only CRUD | all-domains guard on create, delete guard; `#[SuperAdminOnly]` with `#[CrudController]` unverified |
| AdministratorRoleGroup | forbidden-action flash from handler | `DuplicateNameException` message on create/edit; delete guard listing administrators |

When `getListTemplate()/getListViewData()` and a forbidden-action exception land in `AbstractCrudController`,
these nine become a follow-up plan with the same recipe.

## Desired End State

- 9 legacy controllers, their grid factories and `content/<name>/{list,listGrid,new,edit,detail}.html.twig`
  templates are gone; each is replaced by `packages/administration/src/Controller/<Name>Controller.php` +
  `packages/administration/src/Model/<Area>/<Name>CrudHandler.php`.
- Admin menu shows the same items in the same positions (`setMenuSection(..., ['after' => …])`).
- Existing roles are reused via `#[ForRole(AdminRoleConstant::…)]` — no new `ROLE_CRUD_*` role appears in the role matrix.
- Custom behaviour survives as `#[Route]` methods on the CRUD controller (import, export, set-default,
  AJAX helpers) and as top/row `Action`s.
- Smoke test passes with `RouteConfigCustomization` entries renamed.
- Upgrade notes list every renamed route name and every removed class.

### Key Discoveries

- Route paths: `CrudRouteProvider` builds `/admin/{prefix}/{kebab-controller}/`, `/create`, `/edit/{id}`, `/delete/{id}`,
  `/detail/{id}` (`packages/administration/src/Component/Router/CrudRouteProvider.php:27-53,114-122`). The prefix
  keeps only the base path.
- Custom route pattern: `ProductReviewController::approveAction`
  (`packages/administration/src/Controller/ProductReviewController.php:172-190`) — `#[Route(name: 'admin_crud_…')]`
  + `#[CanEdit]` + `#[CsrfProtection]`, entity loaded via `$this->definition->getHandlerForAction(ActionType::EDIT)->getById()`.
- Delete errors: `AbstractCrudController::deleteAction` (`:366-418`) catches any exception from the handler and
  shows a generic flash. Row-level guards are therefore expressed in the UI with
  `RowAction::addCallback()` + `disableWithMessage()` (`packages/administration/src/Component/Action/RowAction.php:73-93`);
  the handler still throws as a safety net.
- Drag & drop: `$datagrid->enableDragAndDrop('position')` (`TransportGroupController.php:47`).
- Domain handling: `setListDomainControl(CrudListDomainControl::SWITCHER)` replaces `AdminDomainTabsFacade`;
  `QUICK_FILTER` replaces `AdminDomainFilterTabsFacade`. Entities implementing `DomainSeparatedEntityInterface`
  get the domain condition automatically (docs `docs/administration/crud-controller/reference/crud-controller.md`).
  `AdminDomainTabsFacade` is already injected into `AbstractCrudController` (`:70-73`) for presetting `domainId` in handlers.
- Computed cells: `virtual` + `transform` for values, `template` for HTML cells (image, badges) —
  `docs/administration/datagrid/fields.md`.
- Handler contract: `TransportGroupCrudHandler` (`packages/administration/src/Model/Transport/TransportGroupCrudHandler.php`).
  Facades differ in signatures (`edit(int $id, …)` vs `edit(Entity, …)`, `delete()` vs `deleteById()`) — handlers absorb that.

## What We're NOT Doing

- Not modifying `AbstractCrudController`, `CrudConfig` or the Datagrid.
- Not converting the 21 excluded controllers listed above.
- Not preserving old route **names** or leaf URL segments; no redirect layer.
- Not changing FormTypes beyond retargeting `back_route` and removing controller-only options.
- Not running tests or coding standards as part of implementation (the author runs them).

## Implementation Approach

Two phases grouped by difficulty; the first controller (ParameterGroup) is the template commit.
Phase 3 writes upgrade notes and cleans `RouteConfigCustomization`.

### Per-controller conversion recipe (applies to every commit)

1. Create `packages/administration/src/Controller/<Name>Controller.php`:
   `#[CrudController(<Entity>::class)]`, `#[ForRole(AdminRoleConstant::<ROLE>)]`, `configure()` with
   `setRoutePrefix()`, `setMenuSection(root, section, ['after' => …])`, `registerHandler()`, optional
   `setListDomainControl()`.
2. Create `packages/administration/src/Model/<Area>/<Name>CrudHandler.php` implementing `CrudHandlerInterface`
   delegating to the existing facade + data factory. Domain preset for new records goes into `createData()`.
3. `configureDatagrid()` — port columns; `virtual` + `transform` for computed columns; `template` for image/badge
   cells (move the cell partial from the old `listGrid.html.twig` into `packages/administration/templates/content/<name>/grid/`).
4. `configureForm()` — `useFormType(<FormType>::class, [...])`; in the FormType change `back_route` to the new
   `admin_crud_<snake>_list`.
5. Extra routes → `#[Route]` methods on the same controller (name `admin_crud_<snake>_<action>`), buttons via
   `configureActions()`; row-level delete guards via `$datagrid->actions()->update('delete', …)` + `addCallback()`/`disableWithMessage()`.
6. Delete: legacy controller, grid factory class, `content/<name>/{list,listGrid,new,edit,detail}.html.twig`
   (keep templates still used by extra routes), menu constants + `addChild()` lines in `SideMenuBuilder`.
7. Grep and fix every reference to the old route names (`RequiredSettingExtension`, other FormTypes, other grids' templates).
8. Rename affected `RouteConfigCustomization` entries.
9. New labels use `t()`; translations come from the standard translation dump.

---

## Phase 1: Plain and domain-switched CRUD controllers

### Overview
Six controllers with list + new/edit/delete, existing FormType, at most a domain switcher and drag & drop.
ParameterGroup is the first commit and serves as the reference for every later commit.

| # | Controller | Entity / Role | `setRoutePrefix` | Menu (`SideMenuBuilder`) | Specifics |
|---|---|---|---|---|---|
| 1 | **ParameterGroup** | `ParameterGroup` / `ROLE_PARAMETER_GROUP` | `/product` | `ROOT_SETTING`, `SECTION_LISTS`, after `LIST_PARAMETER` | `enableDragAndDrop('position')`; column `name` (translatable, resolved automatically); `ParameterGroupFormType['parameterGroup']`, `back_route` at `:47`. Consts 145-147, lines 798-800. |
| 2 | **Navigation** | `NavigationItem` / `ROLE_NAVIGATION` | none | `ROOT_CMS`, no section, after `SECTION_BLOG` | `SWITCHER`; handler `createData()` presets `domainId`; `enableDragAndDrop('position')`; facade `delete(entity)`; `NavigationItemFormType:106`. Consts 90-92, lines 619-621. Delete whole `content/navigation/` dir. |
| 3 | **NotificationBar** | `NotificationBar` / `ROLE_NOTIFICATION_BAR` | none | `ROOT_CMS`, no section, after `AUTOCOMPLETE_SETTING` | `SWITCHER`; virtual `visibility` column via `configureQuery()` CASE (port from `NotificationBarRepository:53-59`); form option `scenario` = `$entity === null ? SCENARIO_CREATE : SCENARIO_EDIT`; validity dates `transform` with "Unlimited" fallback; `NotificationBarFormType:102`. Consts 99-101, lines 634-636. |
| 4 | **Slider** | `SliderItem` / `ROLE_SLIDER_ITEM` | none (URLs lose `item/`) | `ROOT_CMS`, `SECTION_HOMEPAGE`, `'first'` | `SWITCHER`; handler presets `domainId`; `enableDragAndDrop('position')`; `SliderItemFormType['scenario','slider_item']`, `back_route :144`. Consts 95-97, lines 625-627. |
| 5 | **Advert** | `Advert` / `ROLE_ADVERT` | none | `ROOT_CMS`, no section, after `LIST_ARTICLE` | `SWITCHER`; columns `visible` (inverted `hidden`, `transform`), `name`, `preview` (cell `template` rendering `image(row, {type:'web', height:30})`), `positionName` (`transform` via `AdvertPositionRegistry::getAllLabelsIndexedByNames()`); default order `name`; form options `web_image_exists`/`mobile_image_exists` computed in `configureForm()` from `$entity` via `ImageExtension`; `AdvertFormType:190`. Consts 78-80, lines 587-598. |
| 6 | **SalesRepresentative** | `SalesRepresentative` / `ROLE_SALES_REPRESENTATIVE` | none | `ROOT_CUSTOMER`, no section, after `LIST_CUSTOMER` | Columns lastName/firstName/email/telephone; default order `name`; per-row confirm message listing up to 10 customer e-mails via `addCallback()` + `setConfirmMessage()` using `CustomerUserFacade::findEmailsOfCustomerUsersUsingSalesRepresentative()`; facade `edit(entity, data)`; `SalesRepresentativeFormType:111`. Delete `SalesRepresentativeGridFactory`. `RouteConfigCustomization:140-142` `_deleteconfirm` regex no longer matches (route gone). Consts 35-37, lines 373-385. |

### Success Criteria

#### Automated Verification:
- [ ] Smoke test: `docker compose exec php-fpm php phing tests-smoke`
- [ ] Translation dump has no missing keys: `docker compose exec php-fpm php phing translations-dump`
- [ ] PHPStan: `docker compose exec php-fpm php phing phpstan`

#### Manual Verification:
- [ ] Each list shows the same columns and order as before; drag & drop reorders ParameterGroup, Navigation, Slider
- [ ] Menu items appear in the same position; breadcrumb reads "Overview / Editing …"
- [ ] Domain switcher filters Navigation/NotificationBar/Slider/Advert lists and new items land on the selected domain
- [ ] Advert image preview and position label render; NotificationBar visibility column matches the old grid
- [ ] Sales representative delete confirm lists assigned customers

---

## Phase 2: Controllers with extra routes

### Overview
Three controllers that keep 1–3 extra pages/endpoints as `#[Route]` methods on the CRUD controller.

| # | Controller | Entity / Role | Prefix | Menu | Specifics |
|---|---|---|---|---|---|
| 7 | **ClosedDay** | `ClosedDay` / `ROLE_CLOSED_DAYS` | none | `ROOT_SETTING`, `SECTION_LISTS`, after `LIST_STORE` | `SWITCHER`; handler presets `domainId`; facade `deleteById()`; `excludedStores` column via `configureQuery()` join/subselect + cell `template` (badges → `admin_crud_store_edit`); default order `date`; extra route `admin_crud_closed_day_holidays_import` (`#[CanEdit]`, keep `holidaysImport.html.twig`, `HolidaysImportFormType`, `InvalidCountry` handling) + LIST top action "Import holidays"; the old `sub_title` explanation block is dropped. Delete `ClosedDayGridFactory`. Fix `ClosedDayFormType:66`, `TransportFormType:171,181`, `RequiredSettingExtension:333`. Consts 163-166, lines 841-844. |
| 8 | **Store** | `Store` / `ROLE_STORE` | none | `ROOT_SETTING`, `SECTION_LISTS`, after `LIST_PARAMETER_VALUE` | `SWITCHER`; `enableDragAndDrop('position')`; handler `createData()` → `StoreDataFactory::createForDomain(selectedDomainId)`; "default" badge via `name` cell `template`; row callback disables delete for the default store (`disableWithMessage(t('Cannot delete the default store'))`), handler `delete()` also refuses; extra routes `admin_crud_store_set_default/{id}` (row action + EDIT top action, `#[CanEdit]`+`#[CsrfProtection]`) and `admin_crud_store_load_coordinates` (POST, XHR condition, `#[CanView]`, JSON); update `StoreFormType:70,206`, `TransportFormType:180`, `RouteConfigCustomization:133` regex → `admin_crud_store_set_default`. Consts 160-162, lines 837-839. |
| 9 | **PriceList** | `PriceList` / `ROLE_PRICE_LIST` | `/pricing` | `ROOT_PRICING`, no section, `'first'` | `setListDomainControl(QUICK_FILTER)` (replaces namespace `priceList`); columns name/validFrom/validTo/domainId (if multidomain)/validityStatus (cell `template` badge)/lastUpdate, default order `lastUpdate DESC`; handler `createData()` presets `domainId` from the selected quick-filter domain (replaces `?domainId=`); extra routes `admin_crud_price_list_export/{id}` (`#[CanView]`, `CsvResponse`, row + EDIT action), `admin_crud_price_list_import` (`#[CanCreate]`, keep `import.html.twig`, `ImportPriceListFormType`, flash reporting) and `admin_crud_price_list_load_metadata/{id}` (XHR JSON); fix `PriceListFormType:83`, `PriceListOverviewType.html.twig:19`, `ImportPriceListFormType:52,116`; keep `IMPORT_PRICE_LIST` breadcrumb by adding the import route to the CRUD menu item or dropping the constant. Delete `PriceListGridFactory`. Consts 63-66, lines 526-538. |

### Success Criteria

#### Automated Verification:
- [ ] Smoke test passes incl. `admin_crud_store_set_default` (CSRF + 302 entry) and XHR-only routes (skipped by route condition)
- [ ] PHPStan passes; `phpstan-dead-code.neon` has no stale entries for removed classes

#### Manual Verification:
- [ ] Holidays import page reachable from the Closed days list button; import reports count and invalid country
- [ ] Store default badge + set-default button work; delete of the default store is disabled with tooltip; geocoding button on the form still fills coordinates
- [ ] Price list quick filter works; export downloads CSV, import reports errors/warnings, metadata loads in the import form

---

## Phase 3: Upgrade notes, cleanup

### Changes Required

#### 1. Backend upgrade notes
**File**: `upgrade-notes/backend_<YYYYMMDD_HHMMSS>.md` (real timestamp from `date`)
**Changes**: one section for the PR with bullets for
- renamed route names (old → new) for all 9 controllers, and changed URL leaf segments
- removed classes: 9 controllers, grid factories (`SalesRepresentative`, `ClosedDay`, `PriceList`), removed `SideMenuBuilder` constants
- removed templates under `packages/administration/templates/content/<name>/`
- `#project-base-diff` pointer for `RouteConfigCustomization.php`

#### 2. Dead code config
**File**: `phpstan-dead-code.neon`
**Changes**: drop entries for deleted classes.

#### 3. Docs
**File**: `docs/administration/crud-controller/getting-started/creating-a-new-crud-controller.md`
**Changes**: add a short "Migrating a legacy admin controller" section pointing to the recipe above and to
`ParameterGroupController` as the reference.

### Success Criteria

#### Automated Verification:
- [ ] `docker compose exec php-fpm php phing standards` (run by the author)
- [ ] Full test suite green in CI

#### Manual Verification:
- [ ] Administrator role matrix shows no new `ROLE_CRUD_*` rows
- [ ] Pinned menu items and admin search still resolve to the converted pages

---

## Testing Strategy

### Unit Tests
- Handlers are thin delegations; no new unit tests. Existing `packages/administration/tests/Unit` must stay green.

### Smoke Tests
- `HttpSmokeTest` covers every new `admin_crud_*` route automatically; only the `RouteConfigCustomization`
  entries listed per controller need renaming (Store set-default CSRF; `_deleteconfirm` regex becomes unused for this set).

### Manual Testing Steps
1. Log in as superadmin, open each converted list from the menu, compare column set and default order with `20.0`.
2. Create, edit, delete one record per controller; check flash messages and redirect target (list).
3. Switch domain on Navigation/NotificationBar/Slider/Advert/ClosedDay/Store and quick-filter on PriceList; verify filter and new-record domain.
4. Exercise every extra route (holidays import, set-default, load-coordinates, export, import, load-metadata).
5. Log in as an administrator with a restricted role group; verify the same pages are visible/hidden as before.

## Performance Considerations

- SalesRepresentative per-row confirm message issues one query per row; list is small. If it grows, switch to a
  single grouped query in `configureQuery()`.
- ClosedDay `excludedStores` should be fetched with one subselect/join in `configureQuery()`, not per row.

## Migration Notes

- No database changes.
- Projects extending any of the 9 legacy controllers must move to a `#[CrudControllerExtension]` class
  (docs: `extending-existing-crud-controller.md`); projects linking to old route names must update them (upgrade notes).
- Order of commits: ParameterGroup → remaining Phase 1 → Phase 2 → Phase 3. Each commit leaves the application working.

## Estimated effort

| Phase | Controllers | Estimate |
|---|---|---|
| 1 | 6 | 6 × 2 h ≈ 12 h |
| 2 | 3 | 3 × 4 h ≈ 12 h |
| 3 | notes, cleanup | 2 h |
| **Total** | 9 | **≈ 26 h** |

## References

- Existing CRUD controllers: `packages/administration/src/Controller/TransportGroupController.php`,
  `ProductReviewController.php`
- Handler reference: `packages/administration/src/Model/Transport/TransportGroupCrudHandler.php`
- Route provider: `packages/administration/src/Component/Router/CrudRouteProvider.php`
- Docs: `docs/administration/crud-controller/`, `docs/administration/datagrid/fields.md`
- Search for CRUD lists (future unblocking of excluded controllers): branch `mg/crud-search`
