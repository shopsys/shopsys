# Log entity changes

The Shopsys platform allows you to log changes to entities.
Thanks to the mechanism, you can answer the questions like "Who and when changed the order status?", "Why is the product price changed?", etc.

The logging system works to capture changes in the doctrine unit of work before they are flushed.

We used PHP attributes above class, property, and method for setting logging. (https://www.php.net/manual/en/language.attributes.overview.php)

As an implemented sample, you can study the settings on Order, OrderItem, OrderStatus and Country entities.
The principle of use will be described on these examples.

The attributes `Loggable` and `LoggableChild` are used to mark the entity to be logged.
Both of these attributes have logging strategy settings available.
If I want to log all properties in the base, I use the `Loggable(Loggable::STRATEGY_INCLUDE_ALL)` strategy.
If I would like to not log certain properties, I can mark them with the `ExcludeLog` attribute.
Conversely, if I want to log only a few properties from an entity, it would be better to use the `Loggable(Loggable::STRATEGY_EXCLUDE_ALL)` strategy and then mark which properties I want to log using the `Log` attribute.

!!! danger

    **It is possible to log only entities with primary key `id` and method `getId()`**

```php
/**
* @ORM\Table(name="orders")
* @ORM\Entity
*/
#[Loggable(Loggable::STRATEGY_INCLUDE_ALL)]
class Order
{
    ...
```

or

```php
/**
 * @ORM\Table(name="order_items")
 * @ORM\Entity
 */
#[LoggableChild(Loggable::STRATEGY_INCLUDE_ALL)]
class OrderItem
{
    ...
```

!!! danger

    **Extended entities in the App namespace need to be marked with the Loggable or LoggableChild attribute as well.**

The difference between `Loggable` and `LoggableChild` is in the possibility of assigning a log on the entity marked `LoggableChild` under the logs of another assigned entity.
For example, OrderItem is a child entity of the Order entity. In the case of a child entity, it is still necessary to mark its binding property using the `LoggableParentProperty` attribute.
In the case of OrderItem, it's the `$order` property.

```php
/**
 * @ORM\Table(name="order_items")
 * @ORM\Entity
 */
#[LoggableChild(Loggable::STRATEGY_INCLUDE_ALL)]
class OrderItem
{
    ...

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Order
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Order\Order", inversedBy="items")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    #[LoggableParentProperty]
    protected $order;
    ...
}
```

## Option to name the logged record

The administrator wants to see a human-readable record for the logged record.
This is what the `EntityLogIdentify` attribute is used for.
This attribute is set on the method that should return the name of the entity.
For the OrderItem entity, this is the `getName()` method.
Some entities are translatable and you need to mark them like this: `EntityLogIdentify(EntityLogIdentify::IS_LOCALIZED)`.
In the background, the administration locale is inserted when such a method is called.

```php
#[LoggableChild(Loggable::STRATEGY_INCLUDE_ALL)]
class OrderItem
{
    ...

    /**
     * @return string
     */
    #[EntityLogIdentify]
    public function getName()
    {
        return $this->name;
    }

    ...
}
```

There are properties in the Order entity that are not a simple scalar data type.
For example, the status property is of data type OrderStatus or deliveryCountry is of data type Country (another entity).
These entities themselves do not need to be logged, but we want to see the human name on the order in the event of a status change.
The `EntityLogIdentify` attribute is again used for this naming.
So if I want to see in the order log that the status has changed from "New" to "In Progress", I need to mark the getName function on the OrderStatus entity.

```php
/**
 * @ORM\Table(name="order_statuses")
 * @ORM\Entity
 * @method \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTranslation translation(?string $locale = null)
 */
class OrderStatus extends AbstractTranslatableEntity
{
    ...

    #[EntityLogIdentify(EntityLogIdentify::IS_LOCALIZED)]
    /**
     * @param string|null $locale
     * @return string
     */
    public function getName($locale = null): string
    {
        return $this->translation($locale)->getName();
    }

    ...
}
```

## List of results

`Shopsys\FrameworkBundle\Component\EntityLog\Model\Grid\EntityLogGridFactory::createByEntityNameAndEntityId($entityName,$entityId)` is available to display the logs.
You can then write this grid, for example, under the editing form.

```php
$entityLogGrid = $this->entityLogGridFactory->createByEntityNameAndEntityId(
    EntityLogFacade::getEntityNameByEntity($order),
    $order->getId()
);

return $this->render('@ShopsysFramework/Admin/Content/Order/edit.html.twig', [
    ...
    'entityLogGridView' => $entityLogGrid->createView(),
]);
```

and render it

```
{{ entityLogGridView.render() }}
```
