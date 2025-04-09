# Crud Controller

## Actions

The five main actions of the Crud Controller are:

- **List** - Display a list of entities that can be paginated, filtered, and sorted, etc.
- **Detail** - Display detail of an entity (read-only)
- **Create** - Allows to create a new entity. 
- **Edit** - Allows to edit an existing entity.
- **Delete** - Action to delete an entity.

### Routes

Crud controller generates pretty URLs for each action. The URL is generated based on the Controller name.

- `PriceListController` -> `/admin/price-list/` (Same for `PriceListCrudController`)
- `OrderController` -> `/admin/order/`

!!! note

    Base URL can be prefixed by Crud Config method [setRoutePrefix](#setrouteprefixstring-routeprefix)

| ActionType | Route name                            | Route path                            |
|------------|---------------------------------------|---------------------------------------|
| `list`     | `admin_crud_{controller_name}_list`   | `/admin/{controller-name}/`           |
| `detail`   | `admin_crud_{controller_name}_detail` | `/admin/{controller-name}/{entityId}` |
| `create`   | `admin_crud_{controller_name}_create` | `/admin/{controller-name}/create`     |
| `edit`     | `admin_crud_{controller_name}_edit`   | `/admin/{controller-name}/edit`       |
| `delete`   | `admin_crud_{controller_name}_delete` | `/admin/{controller-name}/delete`     |

## Methods

Crud Controller provides several methods that allow you to customize the behavior of the controller.

- `configure()` - Method is used to configure general behavior of controller. Customizable options are available [here](#crud-config).


## Crud Config

The `CrudConfig` class is used to configure the behavior of the Crud Controller. It is used in the `configure` method of the Crud Controller.

### Methods

#### `setTitle(ActionType $actionType, string $title)`

This method allows you to set the title for the given page type. The title is displayed in the page header.

#### `setMenuSection(string $menuSection, ?string $submenuSection = null)`

You can specify where the Crud Controller will be displayed in the administration menu.

`$menuSection` is the name of the root-level menu item.
`$submenuSection` can be used to specify a submenu item.

Examples:
- `$config->setMenuSection('products')` will create Crud controller item under `Products` section
- `$config->setMenuSection('customers', 'promo_codes')` will create Crud controller under `Customers -> Promo Codes` section

#### `visibleInMenu(bool $visible)`

Specify if the Crud Controller should be visible or not in the administration menu. This can be useful if you want to create a controller that is not directly accessible by the user or should be accessed from another controller.

#### `enableAction(ActionType|array $actions)` and `disableAction(ActionType|array $actions)`

Those methods are used to enable or disable actions for the given entity. 

If you want for example to disable the `delete` action you can simply call `$config->disableAction(ActionType::DELETE)`.

#### `disable(bool $disabled)`

Fully disable the Crud Controller. Crud controller will not be accessible by the user.

#### `setRoutePrefix(string $routePrefix)`

Set a prefix for the Crud Controller routes. The prefix is added to the base URL of the Crud Controller.

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