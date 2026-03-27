# Handlers

Handlers enable Create, Read, Update, and Delete operations for your entities. They act as a bridge between your CRUD controllers and your business logic (typically facades).

!!! note

    Basic usage of CRUD handlers is covered in the [Adding Create, Edit, and Delete Actions](../getting-started/adding-create-edit-and-delete-actions.md).

## Using Handlers

Handlers implement specific interfaces based on the actions you need. Each handler method delegates to your facade, which contains the actual business logic for entity operations.

### Interface Hierarchy

Handlers use a hierarchical interface structure where each interface extends the previous one:

```
HandlerInterface (base)
    └── ReadHandlerInterface (adds getById)
        ├── DeleteHandlerInterface (adds delete)
        ├── EditHandlerInterface (adds createDataFromEntity, edit)
        ├── CreateHandlerInterface (adds createData, create)
        └── CrudHandlerInterface extends Delete + Edit + Create (marker for full CRUD)
```

### Choosing the Right Interface

All handler interfaces are located in the `Shopsys\AdministrationBundle\Component\Crud\Handler` namespace. For detailed documentation, see the interface docblocks in the source code.

Implement only the interface that matches your needs:

| Interface | When to Use |
|-----------|-------------|
| `ReadHandlerInterface` | Detail/view-only pages |
| `DeleteHandlerInterface` | Delete functionality |
| `EditHandlerInterface` | Edit functionality (retrieve entity data, save changes) |
| `CreateHandlerInterface` | Create functionality (create empty data, persist new entity) |
| `CrudHandlerInterface` | Full CRUD operations (combines Delete + Edit + Create) |

!!! note "About HandlerInterface"

    `HandlerInterface` is a base marker interface used only for automatic service registration. Do not implement it directly - it provides no functionality. Always implement one of the specific handler interfaces instead.

### Registering Handlers

After creating your handler, register it in your CRUD controller's `configure()` method:

```php
<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Order\OrderCrudHandler;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\FrameworkBundle\Model\Order\Order;

#[CrudController(entityClass: Order::class)]
class OrderController extends AbstractCrudController
{
    public function configure(CrudConfig $config): void
    {
        $config
            ->registerHandler(OrderCrudHandler::class)
        ;
    }
}
```

**Registration Rules:**

- **Automatic action enabling** - Registering a handler automatically enables its corresponding actions in the CRUD controller (e.g., `DeleteHandlerInterface` enables Delete action)
- **One handler per action** - Each action can have only one handler registered. If you want to define your own handler instead of an existing one, you need to unregister the original one first.
- **No mixing handlers** - If you register a handler implementing `CrudHandlerInterface`, you cannot register any other handlers (it claims all actions)
- **Automatic service registration** - Handlers are automatically registered as services - no manual service configuration needed

---

## Hooks System

Hooks allow you to extend CRUD operations with custom logic before, after, or on error conditions. They are implemented in [CRUD controller extensions](../getting-started/extending-existing-crud-controller.md) and provide cross-cutting concerns like logging, caching, and validation without modifying the handler.

### Available Hook Interfaces

All hook interfaces are located in the `Shopsys\AdministrationBundle\Component\Crud\Extension` namespace.

Implement hook interfaces in your CRUD controller extensions:

| Interface | When to Use | Methods Provided |
|-----------|-------------|------------------|
| `CrudDeleteHookExtensionInterface` | Extend delete operations with custom logic | `beforeDelete()`, `afterDelete()`, `onDeleteError()` |

### Hook Execution Flow

When a CRUD action is executed, hooks follow this pattern:

```
1. Entity retrieved (if applicable)
   ↓
2. All before<Action>() hooks executed (in extension priority order)
   ↓
3. Handler action method executed
   ↓
4. All after<Action>() hooks executed (in extension priority order)
   ↓
5. Success flash message shown
   ↓
6. Redirect to list page

--- On Exception ---

1. Exception caught at any step above
   ↓
2. All on<Action>Error() hooks executed
   ↓
3. SilencedExceptionEvent dispatched (database rollback)
   ↓
4. Error flash message shown
   ↓
5. Redirect to list page
```

**Where `<Action>` represents the CRUD operation** (e.g., Delete, Create, Edit)

!!! note "Automatic Flash Messages"

    The system automatically shows generic flash messages for CRUD operations. If you want to show a different message instead:

    - **Success flow**: Add any flash message in your hooks or handler. The default success message will only be shown when no custom messages were added during the operation.
    - **Error flow**: Call `addErrorFlash()` in your `on<Action>Error()` hook to provide a custom user-friendly message. The default error message will only be shown when no other error messages were provided.

**Important Notes:**

- All hooks run within the same database transaction
- If any hook throws an exception, the entire transaction is rolled back
- Multiple extensions can implement hooks - they execute in priority order (lower number = executes first)
- Error hooks (`on<Action>Error`) receive the exception for inspection and custom error handling

---

## Entity Naming

When working with CRUD handlers, it's important to ensure your entities have proper human-readable representations. Entity names are displayed throughout the CRUD interface in:

- Flash messages (e.g., "Order #123 was deleted successfully")
- Page titles and breadcrumbs
- Error messages
- Form labels

### Implementing the `Presentable` Interface

To provide user-friendly entity names, implement the `Shopsys\FrameworkBundle\Component\Utils\Presentable` interface in your entity class:

```php
<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\FrameworkBundle\Component\Utils\Presentable;

class Order implements Presentable
{
    // ... other methods ...

    public function toHumanReadable(): string
    {
        return t('Order with number %number%', ['%number%' => $this->getNumber()]);
    }
}
```

### Best Practices

#### 1. Always Implement `Presentable` interface

```php
// Good - provides meaningful information
public function toHumanReadable(): string
{
    return sprintf('Product "%s" (SKU: %s)', $this->getName(), $this->getSku());
}

// Bad - too generic
public function toHumanReadable(): string
{
    return 'Product';
}
```

#### 2. Include Identifying Information

```php
public function toHumanReadable(): string
{
    // Include the most important identifier
    return t('Customer %name% (%email%)', [
        '%name%' => $this->getFullName(),
        '%email%' => $this->getEmail(),
    ]);
}
```

#### 3. Use Translations

```php
public function toHumanReadable(): string
{
    // Use the t() function for translatable strings
    return t('Category "%name%"', [
        '%name%' => $this->getName(),
    ]);
}
```

#### 4. Handle Empty or Null Values

```php
public function toHumanReadable(): string
{
    $name = $this->getName() ?: t('Unnamed product');
    
    return sprintf('%s (#%d)', $name, $this->getId());
}
```

### Example: Complex Entity Naming

```php
<?php

declare(strict_types=1);

namespace App\Model\Invoice;

use Shopsys\FrameworkBundle\Component\Utils\Presentable;

class Invoice implements Presentable
{
    public function toHumanReadable(): string
    {
        if ($this->getInvoiceNumber()) {
            return t('Invoice %number% for %customer%', [
                '%number%' => $this->getInvoiceNumber(),
                '%customer%' => $this->getCustomer()->getName(),
            ]);
        }
        
        return t('Draft invoice for %customer%', [
            '%customer%' => $this->getCustomer()->getName(),
        ]);
    }
}
```

!!! warning

    The `toHumanReadable()` method should never throw exceptions. Always handle potential null values or missing data gracefully.
