# Extending existing CRUD Controller

This guide will show you how to extend an existing CRUD Controller in your project.

Prerequisite is an existing CrudController class defined for example in `Shopsys\FrameworkBundle\Controller\Admin\OrderCrudController`.

## Create extension class

Create a new class that extends `Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension`. The extension class must also have defined attribute `#[CrudControllerExtension]`.

```php
<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\AdministrationBundle\Component\Attributes\CrudControllerExtension;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension;
use Shopsys\FrameworkBundle\Controller\Admin\OrderCrudController;

#[CrudControllerExtension(crudController: OrderCrudController::class)]
class OrderControllerExtension extends AbstractCrudControllerExtension
{

}
```

Class will be automatically registered as a service and will be used as an extension for the `OrderCrudController`.

## Override CRUD controller

That's it! Now you can override methods defined in `AbstractCrudControllerExtension` that copies the methods from the original Crud Controller.

```php
// OrderControllerExtension.php

public function configure(CrudConfig $config): void
{
    $config
        ->setRoutePrefix('/my-prefix/')
    ;
}

public function configureDatagrid(Datagrid $datagrid): void
{
    $datagrid
        ->remove('number')
        ->add('city', [
            'label' => t('City'),
        ])
    ;
}
```

### Using Hooks

Extensions can implement hook interfaces to add custom logic before, after, or on error during CRUD operations.
See [Hooks System Reference](../reference/handlers.md#hooks-system) for complete documentation.

```php
<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\AdministrationBundle\Component\Attributes\CrudControllerExtension;
use Shopsys\AdministrationBundle\Component\Crud\Extension\CrudEditHookExtensionInterface;
use Shopsys\AdministrationBundle\Component\Crud\Extension\CrudCreateHookExtensionInterface;
use Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension;
use Shopsys\FrameworkBundle\Controller\Admin\OrderCrudController;
use Throwable;

#[CrudControllerExtension(crudController: OrderCrudController::class)]
class OrderControllerExtension extends AbstractCrudControllerExtension implements CrudEditHookExtensionInterface, CrudCreateHookExtensionInterface
{
    public function beforeEdit(object $entity, object $data): void
    {
        // Custom logic before saving edited entity
    }

    public function afterEdit(object $entity, object $data): void
    {
        // Custom logic after successful edit (e.g., clear cache, send notification)
    }

    public function onEditError(object $entity, object $data, Throwable $exception): void
    {
        // Custom error handling for edit failures
    }

    public function beforeCreate(object $data): void
    {
        // Custom logic before creating new entity
    }

    public function afterCreate(object $entity, object $data): void
    {
        // Custom logic after successful creation
    }

    public function onCreateError(object $data, Throwable $exception): void
    {
        // Custom error handling for create failures
    }
}
```

## Multiple extensions for one CRUD Controller

You can also specify multiple extension classes for one CRUD Controller. This is useful when building a complex system with multiple modules.

### Priority

For that purpose, you can use the `priority` parameter in the `#[CrudControllerExtension]` attribute.

A higher priority value means that the extension will be executed later. The default priority is `0`. Extension from the `App` namespace will be automatically registered with the highest priority if the `priority` parameter is not set.
