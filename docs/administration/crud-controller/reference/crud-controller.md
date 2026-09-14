# CRUD Controller

## Actions

The five main actions of the CRUD Controller are:

- **List** - Display a list of entities that can be paginated, filtered, and sorted, etc.
- **Detail** - Display detail of an entity (read-only)
- **Create** - Allows to create a new entity. 
- **Edit** - Allows to edit an existing entity.
- **Delete** - Action to delete an entity.

!!! note

    Create, Edit, Detail, and Delete actions require a [Handler](handlers.md) to be configured.

### Routes

Crud controller generates pretty URLs for each action. The URL is generated based on the Controller name.

- `PriceListController` -> `/admin/price-list/` (Same for `PriceListCrudController`)
- `OrderController` -> `/admin/order/`

!!! note

    Base URL can be prefixed by CRUD Config method [setRoutePrefix](#setrouteprefixstring-routeprefix)

| ActionType | Route name                            | Route path                                   |
|------------|---------------------------------------|----------------------------------------------|
| `list`     | `admin_crud_{controller_name}_list`   | `/admin/{controller-name}/`                  |
| `detail`   | `admin_crud_{controller_name}_detail` | `/admin/{controller-name}/detail/{entityId}` |
| `create`   | `admin_crud_{controller_name}_create` | `/admin/{controller-name}/create`            |
| `edit`     | `admin_crud_{controller_name}_edit`   | `/admin/{controller-name}/edit/{entityId}`   |
| `delete`   | `admin_crud_{controller_name}_delete` | `/admin/{controller-name}/delete/{entityId}` |

### Role constant

Role constant used for permission checking is generated automatically from the controller name (e.g. `PriceListController` -> `ROLE_CRUD_PRICE_LIST`) and registered in the administrator role matrix.
If you want the controller to use an existing role instead, declare it with the `#[ForRole]` attribute on the controller class:

```php
#[CrudController(TransportGroup::class)]
#[ForRole(AdminRoleConstant::ROLE_TRANSPORT_AND_PAYMENT)]
class TransportGroupController extends AbstractCrudController
```

The role is then used by the built-in CRUD actions as well as by permission attributes on custom routes, and the controller does not register its own role in the role matrix — the referenced role must be provisioned by a role provider.
The attribute can also be placed on a [CRUD Controller extension](../getting-started/extending-existing-crud-controller.md) class to override the role of the extended controller.

See [Admin Rights and Access Control](../../admin-rights.md)

## Methods

Crud Controller provides several methods that allow you to customize the behavior of the controller.
These methods can be overridden to customize the controller behavior:

### `configure(CrudConfig $config): void`

Configure general behavior of the controller. Customizable options are available [here](#crud-config).

```php
protected function configure(CrudConfig $config): void
{
    $config
        ->setEntityNameSingular(t('Product'))
        ->setEntityNamePlural(t('Products'))
        ->setMenuTitle(t('Products management'))
        ->setMenuSection('products');
}
```

### `configureActions(ActionsConfig $actions): void`

Configure actions for different page types. See [Actions](actions.md) for more details.

```php
protected function configureActions(ActionsConfig $actions): void
{
    $actions->add(
        ActionType::LIST,
        Action::create('export', t('Export'))
            ->linkToRoute('admin_product_export')
    );
}
```

### `configureDatagrid(Datagrid $datagrid): void`

Configure the datagrid for the list page. See [Configuring List Page](../getting-started/configure-list-page.md) for examples.

```php
protected function configureDatagrid(Datagrid $datagrid): void
{
    $datagrid
        ->add('number', [
            'label' => t('Order Nr.'),
        ])
        ->add('createdAt', [
            'label' => t('Created at'),
        ])
        ->add('domainId', [
            'label' => t('Domain ID'),
        ])
    ;
}
```

### `configureQuery(QueryBuilder $queryBuilder): void`

Modify the query used to fetch entities for the list page.

```php
protected function configureQuery(QueryBuilder $queryBuilder): void
{
    $queryBuilder
        ->andWhere('o.deleted = :deleted')
        ->setParameter('deleted', false);
}
```

### List domain control

A list of an entity implementing `\Shopsys\FrameworkBundle\Component\Domain\Entity\DomainSeparatedEntityInterface` displays the **domain filter** above the datagrid and filters the list query by the selected domain **automatically** — no `configure()` or `configureQuery()` code is needed.

A selected domain limits the list to that domain, and the "All domains" option limits it to the domains available to the administrator.
The filter remembers its selection under a namespace generated from the controller name, and hides itself when only one domain is available.

Use `setListDomainControl()` in `configure()` to change the default:

```php
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlType;

public function configure(CrudConfig $config): void
{
    $config->setListDomainControl(DomainControlType::FILTER, [1, 3], 'crud_shared_selection');
}
```

- `DomainControlType::FILTER` displays a per-list filter with an "All domains" option.
  The optional `$allowedDomainIds` argument restricts the filter to the specified domain IDs; the list is always intersected with the domains available to the administrator.
  The optional `$filterNamespace` argument overrides the generated namespace — pass the same value in two controllers to share the selection between their lists.
- `DomainControlType::SWITCHER` displays the global administration domain switcher.
  It always returns one selected domain ID and supports neither `$allowedDomainIds` nor `$filterNamespace`.
  Use it on lists whose create and edit actions are bound to the globally selected domain.
- `DomainControlType::NONE` displays no domain control and applies no domain condition. Use it to opt an entity belonging to a domain out of the automatic filtering.

The domain condition is applied whenever the control is enabled, even when only one domain is available — a list must never show records of a domain the administrator has no access to.

#### Automatic domain field

When the list works with more than one domain, a `domainId` field displaying the domain of every record is added to the datagrid automatically as the last one.

Adding the field yourself takes precedence over the automatic one, which is the way to change its label, position or visibility:

```php
protected function configureDatagrid(Datagrid $datagrid): void
{
    $datagrid
        ->add('name', ['label' => t('Name')])
        ->add('domainId', ['label' => t('Shop')]);
}
```

Displaying the domain and filtering by it are independent — hiding the field (`'visible' => false`) never widens the listed records. Use `DomainControlType::NONE` to turn the filtering off as well.

#### Entities without `DomainSeparatedEntityInterface`

Such an entity displays no domain control and no domain condition. Enable the control with `setListDomainControl()` when the list should offer the domain choice for other reasons — the domain is related in another way (e.g. through a joined entity), so nothing is applied to the list query automatically.

**Showing per-domain values in a column** (status, publish date, …) — select the value for the selected domain, or aggregate it over the domains of the list when "All domains" is selected. Inject `DomainControlScopeFactory` into your controller to resolve the same scope the datagrid works with:

```php
protected function configureQuery(QueryBuilder $queryBuilder): void
{
    $queryBuilder
        ->addSelect(sprintf(
            '(SELECT MIN(bad.status) FROM %s bad WHERE bad.blogArticle = o AND bad.domainId IN (:domainIds)) AS domainStatus',
            BlogArticleDomain::class,
        ))
        ->setParameter('domainIds', $domainControlScope->getEffectiveDomainIds());
}
```

`getEffectiveDomainIds()` returns the selected domain, or the domains of the list when "All domains" is selected.
A subselect is used instead of a join to keep one row per entity, and each subselect needs its own DQL alias, as aliases are unique within the whole query.

Such a value does not map to a property of the listed entity, so display it with a `virtual` datagrid field whose `transform` reads it from the row:

```php
$datagrid->add('status', [
    'label' => t('Status'),
    'virtual' => true,
    'transform' => fn (mixed $value, array $row): mixed => $row['domainStatus'] ?? null,
]);
```

### `configureForm(CrudFormConfigurator $formConfigurator, ?object $entity = null): void`

Configure the form for create and edit pages. The `$entity` parameter is `null` for create action and contains the entity being edited for edit action.

The `CrudFormConfigurator` provides two mutually exclusive approaches — you must pick one:

**Use an existing FormType class:**

```php
protected function configureForm(CrudFormConfigurator $formConfigurator, ?object $entity = null): void
{
    $formConfigurator->useFormType(BrandFormType::class, [
        'brand' => $entity,
    ]);
}
```

**Or build the form inline using the builder:**

```php
protected function configureForm(CrudFormConfigurator $formConfigurator, ?object $entity = null): void
{
    $formConfigurator->useBuilder()
        ->add('name', TextType::class, [
            'label' => t('Name'),
            'required' => true,
        ])
        ->add('description', TextareaType::class, [
            'label' => t('Description'),
            'required' => false,
        ]);
}
```

!!! warning "Mutually exclusive modes"

    Calling `useFormType()` after `useBuilder()` (or vice versa) throws `CrudFormAlreadyConfiguredException`. This also applies to [extensions](../getting-started/extending-existing-crud-controller.md#extending-forms) — if the controller uses `useFormType()`, extensions cannot call `useBuilder()`. When using `useBuilder()`, extensions can call `useBuilder()` too and will receive the same builder instance to add their fields.

## CRUD Config

The `CrudConfig` class is used to configure the behavior of the Crud Controller. It is used in the `configure` method of the Crud Controller.

### Titles, breadcrumbs and pretitle

All page labels are derived from a single source — the **singular** and **plural** entity name — which are generated automatically from the entity class name using English singular/plural inflection. For example, entity `OrderItem` produces singular "Order item" and plural "Order items". These generated names are registered as translation keys automatically — no manual `t()` wrapping is needed for defaults.

The action and the entity name are never concatenated into a single declined phrase (such as "Editing Order item"), because that is grammatically broken in inflected languages (e.g. Czech "Editace Sklad"). Instead each label keeps the entity name in the nominative case and conveys the action separately:

| Page   | Pretitle (above the heading) | Heading & browser tab title    | Breadcrumb                            |
|--------|------------------------------|--------------------------------|---------------------------------------|
| list   | `Overview`                   | plural (e.g. "Orders")         | plural (menu label)                   |
| create | `New record`                 | singular (e.g. "Order")        | `New record`                          |
| edit   | `Editing`                    | singular `·` record name       | `Editing a record - {record name}`    |
| detail | `Detail`                     | singular `·` record name       | `Record detail - {record name}`       |

- The **pretitle** is a static, action-specific label rendered in the page templates.
- The **heading** (and browser tab title) is the entity name; for edit/detail the concrete record's human-readable name is appended after a `·` separator.
- The **breadcrumb** uses a generic, entity-independent phrase (using the word "record") so it stays grammatically correct; for edit/detail it is overridden at runtime with the record name.

For the record name to be shown, the entity returned by the handler's `getById()` must implement [`Presentable`]({{github.link}}/packages/framework/src/Component/Utils/Presentable.php) (`toHumanReadable()`); this is required by the `ReadHandlerInterface` contract.

### Methods

#### `setEntityNameSingular(string $entityNameSingular)` and `setEntityNamePlural(string $entityNamePlural)`

Override the automatically derived singular/plural entity name. Wrap the value in `t()` so it gets picked up for translation. Set these only when the automatic English inflection is wrong (e.g. irregular plurals) or you want a different label than the entity class name.

```php
$config
    ->setEntityNameSingular(t('Order'))
    ->setEntityNamePlural(t('Orders'));
```

#### `setMenuTitle(string $menuTitle)`

Sets a custom title for the menu item. By default, the pluralized entity name is used (e.g. "Orders" for `Order` entity).

```php
$config->setMenuTitle(t('Orders management'));
```

#### `enableAction(ActionType|array $actions)` and `disableAction(ActionType|array $actions)`

Those methods are used to enable or disable actions for the given entity.

If you want for example to disable the `delete` action you can simply call `$config->disableAction(ActionType::DELETE)`.

!!! note

     You can only enable/disable actions that have a corresponding handler registered. More about handlers can be found in the [Handlers](../reference/handlers.md) reference.

#### `disable(bool $disabled)`

Fully disable the CRUD Controller that will not be accessible by the user.

#### `setCustomRoleSection(string $roleSection)`

By default, Menu section is used as role section. If you want to use a custom role section, you can set it using this method.

Role sections can be found in the `Shopsys\AdministrationBundle\Component\Security\Role\AdminRoleSectionsProvider` class.

#### `setRoutePrefix(string $routePrefix)`

Set a prefix for the CRUD Controller routes. The prefix is added to the base URL of the CRUD Controller.

Example:

```php
// ProductsController

    protected function configure(CrudConfig $config): void
    {
        $config
            ->setRoutePrefix('/new/')
        ;
    }
```

The URL for the `list` action will be `/admin/new/products/` instead of `/admin/products/`.


#### `visibleInMenu(bool $visible)`

Specify if the CRUD Controller should be visible or not in the administration menu. This can be useful if you want to create a controller that is not directly accessible by the user or should be accessed from another controller.

#### `setMenuSection(string $menuSection, ?string $submenuSection = null, string|array $position = 'last')`

You can specify where the Crud Controller will be displayed in the administration menu.

`$menuSection` is the name of the root-level menu item.
`$submenuSection` can be used to specify a submenu item.
`$position` controls the order of the item among its siblings in the target (sub)section. It accepts `'first'`, `'last'` (default), `['before' => '<siblingName>']`, or `['after' => '<siblingName>']`, where the sibling name is the menu item name of an existing sibling. When the referenced sibling is not present, the item is appended last. See [Positioning menu items](../../administration-menu.md#positioning-menu-items) for details.

Examples:
- `$config->setMenuSection('products')` will create Crud controller item under `Products` section
- `$config->setMenuSection('customers', 'promo_codes')` will create Crud controller under `Customers -> Promo Codes` section
- `$config->setMenuSection('settings', 'lists', ['after' => 'transports_and_payments'])` will place the item in the `Settings -> Lists` section right after the `transports_and_payments` item

#### `setMenuIcon(string $icon)`

Set an icon for the root-level CRUD menu item created by this controller. The icon is only applied when the controller is placed directly under the menu root (i.e. when `menuSection` is the root).
Icons are not used for CRUD items in nested menu sections.

Example:

```php
$config
    ->setMenuIcon('cart')
;
```
