# Creating a new Crud Controller

This guide will show you how to create a new Crud Controller for your project.

## 1. Create a new Crud Controller

Create a new Crud Controller by extending the `Shopsys\AdministrationBundle\Controller\AbstractCrudController` class and on your class, define attribute `#[CrudController]` with the entity class name.

```php

<?php

declare(strict_types=1);

namespace App\Controller\Admin;

...

#[CrudController(Order::class)]
class OrderController extends AbstractCrudController
{
    ...
}

```

That's it! Now you have a new Crud Controller that is automatically registered as a service and will be available in the administration.

## 2. Configure Crud Controller

Now you can implement `configure()` method that allows you to customize some general behavior of the controller. More information about available options can be found in the [Crud Config](../reference/crud-controller.md#crud-config) reference.

```php

public function configure(CrudConfig $config): void
{
    $config
        ->setTitle(ActionType::LIST, t('My new Crud Controller')) // Set the title of the list page
        ->setMenuSection('customers') // Set the menu section where the controller will be placed
        ->disableAction(ActionType::DELETE) // You can disable some actions
    ;
}
```

As a next step, you can continue with [Configuring List Page](configure-list-page.md) guide.
