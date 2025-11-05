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
        └── DeleteHandlerInterface (adds delete)
            └── CrudHandlerInterface (marker for full CRUD)
```

### Choosing the Right Interface

All handler interfaces are located in the `Shopsys\AdministrationBundle\Component\Crud\Handler` namespace. For detailed documentation, see the interface docblocks in the source code.

Implement only the interface that matches your needs:

| Interface | When to Use |
|-----------|-------------|
| `ReadHandlerInterface` | Detail/view-only pages |
| `DeleteHandlerInterface` | Delete functionality |
| `CrudHandlerInterface` | Full CRUD operations |

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

## Entity Naming

When working with CRUD handlers, it's important to ensure your entities have proper string representations. Entity names are displayed throughout the CRUD interface in:

- Flash messages (e.g., "Order #123 was deleted successfully")
- Page titles and breadcrumbs
- Error messages
- Form labels

### Implementing `__toString()` Method

To provide user-friendly entity names, implement the `__toString()` method in your entity class:

```php
<?php

declare(strict_types=1);

namespace App\Model\Order;

class Order
{
    // ... other methods ...
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        return t('Order with number %number%', ['%number%' => $this->getNumber()]);
    }
}
```

### How Entity Names Are Resolved

The CRUD system uses `Shopsys\AdministrationBundle\Component\Doctrine\ObjectNameHelper::getObjectName()` to determine entity names:

1. **If the entity implements `Stringable` interface** (which includes having a `__toString()` method), that string representation is used
2. **Otherwise**, a technical fallback is generated: `ClassName@objectHash`

### Best Practices

#### 1. Always Implement `__toString()`

```php
// Good - provides meaningful information
public function __toString(): string
{
    return sprintf('Product "%s" (SKU: %s)', $this->getName(), $this->getSku());
}

// Bad - too generic
public function __toString(): string
{
    return 'Product';
}
```

#### 2. Include Identifying Information

```php
public function __toString(): string
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
public function __toString(): string
{
    // Use the t() function for translatable strings
    return t('Category "%name%"', [
        '%name%' => $this->getName(),
    ]);
}
```

#### 4. Handle Empty or Null Values

```php
public function __toString(): string
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

class Invoice
{
    public function __toString(): string
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

    The `__toString()` method should never throw exceptions. Always handle potential null values or missing data gracefully.
