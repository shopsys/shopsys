# Adding a New Order Item Type

In this cookbook, we will add a new order item type, namely an additional service.
We will see how to create a new order item type, how to add it to the administration, and how to handle it in the order process.

## New order item type

The first step to creating a new order item type is creating a new class extending `OrderItem` in `app/src/Model/Order/Item`
and adding a new constant and the useful methods.
Those methods may not be necessary for all new order item types, but they are good to have for consistency and future improvements.

```php
namespace App\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem as BaseOrderItem;

class OrderItem extends BaseOrderItem {
    const TYPE_SERVICE = 'service';

    // ...

    /**
     * @return bool
     */
    public function isTypeService(): bool
    {
        return $this->isType(self::TYPE_SERVICE);
    }

    protected function checkTypeService(): void
    {
        $this->checkTypeOf(self::TYPE_SERVICE);
    }
```

Then we need to create a new class extending `OrderItemFactory` in `app/src/Model/Order/Item` and add a new method for creating the new order item type.

```php
namespace App\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory as BaseOrderItemFactory;

class OrderItemFactory extends BaseOrderItemFactory {

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $orderItemData
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \App\Model\Order\Item\OrderItem
     */
    public function createServiceByOrderItemData(
        OrderItemData $orderItemData,
        Order $order,
    ): OrderItem {
        return $this->createOrderItem(
            $orderItemData,
            $order,
        );
    }
```

## Use the new order item type

Let's hook our new type into the order process.

<!TODO - add the rest of the cookbook after finish implementation of the middleware>
