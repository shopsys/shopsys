# Creating a new Crud Controller

This guide will show you how to create a new Crud Controller in your project.

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

protected function configure(CrudConfig $config): CrudConfig
{
    return $config
        ->setTitle(PageType::LIST, t('My new Crud Controller'))
        ->setMenuSection('customers')
        ->disableAction(ActionType::DELETE)
    ;
}
```

## 3. Define Datagrid

The next step is to define a Datagrid that will be used to display the list of records. The list of methods that can be used to configure Datagrid can be found in the [Datagrid Methods Configuration](../../datagrid/configuration.md#methods-configuration) reference.

```php

public function configureDatagrid(Datagrid $datagrid): Datagrid
{
    $datagrid
        ->addIdentifier('id')
        ->add('name', [
            'label' => t('Name'),
        ])
    ;

    return $datagrid;
}
```

Every column can be configured by passing an array with options. The list of available options can be found in the [Fields Configuration](../../datagrid/fields.md) reference.
