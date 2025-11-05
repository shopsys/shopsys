# Adding Create, Edit, and Delete Actions

By default, only the List action is enabled when you create a CRUD controller. This step-by-step guide shows you how to add Create, Edit, Detail, and Delete actions by implementing a CRUD handler.

## 1. Create a CRUD handler

The handler acts as a bridge between your CRUD controller and your business logic (typically facades). Create a new class that implements any of extended interfaces of `Shopsys\AdministrationBundle\Component\Crud\Handler\HandlerInterface`

> **Note:** For guidance on choosing the right handler interface for your needs, see [Choosing the Right Interface](../reference/handlers.md#choosing-the-right-interface).

```php
<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\AdministrationBundle\Component\Crud\Handler\DeleteHandlerInterface;

class OrderDeleteHandler implements DeleteHandlerInterface
{
    public function __construct(
        private readonly OrderFacade $orderFacade,
    ) {
    }

    public function getById(int $id): object
    {
        return $this->orderFacade->getById($id);
    }

    public function delete(object $entity): void
    {
        $this->orderFacade->deleteById($entity->getId());
    }
}
```

## 2. Register the Handler in Your Controller

```php

<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Order\Order;
use App\Model\Order\OrderDeleteHandler;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;

#[CrudController(entityClass: Order::class)]
class OrderController extends AbstractCrudController
{
    public function configure(CrudConfig $config): void
    {
        $config
            ->registerHandler(OrderDeleteHandler::class)
        ;
    }
}
```

This will enable Delete action in your CRUD controller. Similarly, you can create and register handlers for Create, Edit, and Detail actions by implementing the respective interfaces.

## 3. Implement Entity String Representation

For user-friendly messages in the admin interface, implement the `__toString()` method in your entity class:

```php
<?php

declare(strict_types=1);

namespace App\Model\Order;

class Order
{
    // ... other properties and methods ...
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        return t('Order #%number%', ['%number%' => $this->getNumber()]);
    }
}
```

Read more about implementing `__toString()` in the [Entity Naming](../reference/handlers.md#entity-naming) section.
