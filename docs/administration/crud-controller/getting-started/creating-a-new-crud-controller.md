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

You can implement the `configure()` method to customize the general behavior of the controller:

```php
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;

public function configure(CrudConfig $config): void
{
    $config
        ->setTitle(ActionType::LIST, t('Orders')) // Set the title of the list page
        ->setMenuSection('customers') // Set the menu section where the controller will be placed
    ;
}
```

More configuration options can be found in the [Crud Config](../reference/crud-controller.md#crud-config) reference.

## Next Steps

- Continue with [Configuring List Page](configure-list-page.md) to customize your datagrid
- Learn how to [Add Create, Edit, and Delete Actions](adding-create-edit-and-delete-actions.md)
