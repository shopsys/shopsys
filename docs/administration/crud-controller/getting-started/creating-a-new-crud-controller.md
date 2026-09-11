# Creating a new Crud Controller

This guide will show you how to create a new Crud Controller for your project.

## 1. Create a new Crud Controller

Create a new Crud Controller by extending the `Shopsys\AdministrationBundle\Controller\AbstractCrudController` class and on your class, define attribute `#[CrudController]` with the entity class name.

```php

<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Order\Order;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;

#[CrudController(Order::class)]
class OrderController extends AbstractCrudController
{
    // That's it! The list page is now available
}

```

That's it! Now you have a new Crud Controller that is automatically registered as a service and will be available in the administration. By default, only the List action is enabled.

## 2. Configure Crud Controller (Optional)

You can implement the `configure()` method to customize the controller behavior:

```php
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;

public function configure(CrudConfig $config): void
{
    $config
        ->setEntityNamePlural(t('Orders')) // Override the auto-derived plural entity name (used in the list title and menu)
        ->setMenuSection('customers') // Set the menu section where the controller will be placed
    ;
}
```

!!! info

    Default page titles and menu labels are generated automatically from the entity class name.


More configuration options can be found in the [Crud Config](../reference/crud-controller.md#crud-config) reference.

## Next Steps

- Continue with [Configuring List Page](configure-list-page.md) to customize your datagrid
- Learn how to [Add Create, Edit, and Delete Actions](adding-create-edit-and-delete-actions.md)

## Migrating a legacy admin controller

A legacy `AdminBaseController` with list/new/edit/delete actions is replaced by one CRUD controller and one handler, see `Shopsys\AdministrationBundle\Controller\ParameterGroupController` for the reference conversion.

1. create the CRUD controller with `#[CrudController(<Entity>::class)]` and `#[ForRole(AdminRoleConstant::<existing role>)]`, keep the URL base with `setRoutePrefix()` and the menu position with `setMenuSection(..., ['after' => ...])`
2. create a handler implementing `CrudHandlerInterface` that delegates to the existing facade and data factory; preset the selected domain in `createData()` when the list uses `setListDomainControl()`
3. make the entity implement `Shopsys\FrameworkBundle\Component\Utils\Presentable`
4. port the grid columns to `configureDatagrid()` — use `virtual` + `transform` for computed values and `template` for HTML cells
5. keep extra pages (import, export, ...) as `#[Route]` methods on the CRUD controller named `admin_crud_<snake>_<action>` and expose them with `configureActions()`
6. delete the legacy controller, its grid factory, its `content/<name>/{list,listGrid,new,edit,detail}.html.twig` templates and its `SideMenuBuilder` items, then update every reference to the old route names (`back_route` of the form type, other templates, smoke test customizations) and describe the renamed routes in the upgrade notes
