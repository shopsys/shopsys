# Extending existing Crud Controller

This guide will show you how to extend an existing Crud Controller in your project.

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

## Override crud controller

That's it! Now you can override methods defined in `AbstractCrudControllerExtension` that copies the methods from the original Crud Controller.

```php
// OrderControllerExtension.php

public function configure(CrudConfig $config): CrudConfig
{
    $config
        ->setRoutePrefix('/my-prefix/')
    ;

    return $config;
}

public function configureDatagrid(Datagrid $datagrid): Datagrid
{
    $datagrid
        ->remove('number')
        ->add('city', [
            'label' => t('City'),
        ])
    ;

    return $datagrid;
}
```

## Multiple extensions for one Crud Controller

You can also specify multiple extension classes for one Crud Controller. This may come in handy when you are building a complex system with multiple modules.

### Priority

For that purpose you can use the `priority` parameter in the `#[CrudControllerExtension]` attribute.

Bigger priority value means that the extension will be executed later. The default priority is `0`. Extension from `App` namespace will be automatically registered with the highest priority if the `priority` parameter is not set.
