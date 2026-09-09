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
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Webmozart\Assert\Assert;

class OrderDeleteHandler implements DeleteHandlerInterface
{
    public function __construct(
        private readonly OrderFacade $orderFacade,
    ) {
    }

    public function getById(int $id): Presentable
    {
        return $this->orderFacade->getById($id);
    }

    public function delete(Presentable $entity): void
    {
        Assert::isInstanceOf($entity, Order::class);

        $this->orderFacade->deleteById($entity->getId());
    }
}
```

!!! tip "Narrow the `object` parameters with `Assert::isInstanceOf()`"

    Handler interfaces declare entities as the generic `Presentable` and data objects as plain `object`, because the interfaces are shared by all CRUD controllers.
    Start every method that receives an entity or a data object with `Assert::isInstanceOf()` (from `webmozart/assert`).
    The assert fails fast with a clear message when the handler is registered for a wrong entity, and thanks to
    the `phpstan/phpstan-webmozart-assert` extension PHPStan narrows the type, so calls like `$entity->getId()`
    or passing `$data` to a typed facade method are analysed properly instead of being reported as errors.

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

This will enable Delete action in your CRUD controller.

### Edit Handler Example

To enable edit functionality, implement `EditHandlerInterface`:

```php
<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\AdministrationBundle\Component\Crud\Handler\EditHandlerInterface;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Webmozart\Assert\Assert;

class OrderEditHandler implements EditHandlerInterface
{
    public function __construct(
        private readonly OrderFacade $orderFacade,
        private readonly OrderDataFactory $orderDataFactory,
    ) {
    }

    public function getById(int $id): Presentable
    {
        return $this->orderFacade->getById($id);
    }

    public function createDataFromEntity(Presentable $entity): object
    {
        Assert::isInstanceOf($entity, Order::class);

        return $this->orderDataFactory->createFromOrder($entity);
    }

    public function edit(Presentable $entity, object $data): void
    {
        Assert::isInstanceOf($entity, Order::class);
        Assert::isInstanceOf($data, OrderData::class);

        $this->orderFacade->edit($entity->getId(), $data);
    }
}
```

### Create Handler Example

To enable create functionality, implement `CreateHandlerInterface`:

```php
<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\AdministrationBundle\Component\Crud\Handler\CreateHandlerInterface;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Webmozart\Assert\Assert;

class OrderCreateHandler implements CreateHandlerInterface
{
    public function __construct(
        private readonly OrderFacade $orderFacade,
        private readonly OrderDataFactory $orderDataFactory,
    ) {
    }

    public function getById(int $id): Presentable
    {
        return $this->orderFacade->getById($id);
    }

    public function createData(): object
    {
        return $this->orderDataFactory->create();
    }

    public function create(object $data): Presentable
    {
        Assert::isInstanceOf($data, OrderData::class);

        return $this->orderFacade->create($data);
    }
}
```

### Full CRUD Handler

To enable all operations at once, implement `CrudHandlerInterface` which combines Delete, Edit, and Create:

```php
<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\AdministrationBundle\Component\Crud\Handler\CrudHandlerInterface;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Webmozart\Assert\Assert;

class OrderCrudHandler implements CrudHandlerInterface
{
    public function __construct(
        private readonly OrderFacade $orderFacade,
        private readonly OrderDataFactory $orderDataFactory,
    ) {
    }

    public function getById(int $id): Presentable
    {
        return $this->orderFacade->getById($id);
    }

    public function delete(Presentable $entity): void
    {
        Assert::isInstanceOf($entity, Order::class);

        $this->orderFacade->deleteById($entity->getId());
    }

    public function createDataFromEntity(Presentable $entity): object
    {
        Assert::isInstanceOf($entity, Order::class);

        return $this->orderDataFactory->createFromOrder($entity);
    }

    public function edit(Presentable $entity, object $data): void
    {
        Assert::isInstanceOf($entity, Order::class);
        Assert::isInstanceOf($data, OrderData::class);

        $this->orderFacade->edit($entity->getId(), $data);
    }

    public function createData(): object
    {
        return $this->orderDataFactory->create();
    }

    public function create(object $data): Presentable
    {
        Assert::isInstanceOf($data, OrderData::class);

        return $this->orderFacade->create($data);
    }
}
```

## 3. Implement Entity String Representation

For user-friendly messages in the admin interface, implement the `Shopsys\FrameworkBundle\Component\Utils\Presentable` interface in your entity class:

```php
<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\FrameworkBundle\Component\Utils\Presentable;

class Order implements Presentable
{
    // ... other properties and methods ...

    public function toHumanReadable(): string
    {
        return t('Order #%number%', ['%number%' => $this->getNumber()]);
    }
}
```

Read more about implementing `Presentable` interface in the [Entity Naming](../reference/handlers.md#entity-naming) section.

## 4. Configure Form

To display forms on create and edit pages, override the `configureForm()` method in your CRUD controller. The method receives a `CrudFormConfigurator` and the entity being edited (`null` for create).

You can use an existing FormType class:

```php
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;

protected function configureForm(CrudFormConfigurator $formConfigurator, ?Presentable $entity = null): void
{
    $formConfigurator->useFormType(OrderFormType::class, [
        'order' => $entity,
    ]);
}
```

Or build the form inline using the builder:

```php
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Symfony\Component\Form\Extension\Core\Type\TextType;

protected function configureForm(CrudFormConfigurator $formConfigurator, ?Presentable $entity = null): void
{
    $formConfigurator->useBuilder()
        ->add('name', TextType::class, [
            'label' => t('Name'),
            'required' => true,
        ]);
}
```

See [configureForm reference](../reference/crud-controller.md#configureformcrudformconfigurator-formconfigurator-presentable-entity-null-void) for more details.
