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
#[ORM\Table(name: 'orders')]
#[ORM\Entity]
#[Loggable(Loggable::STRATEGY_INCLUDE_ALL)]
class Order
{
    ...
```

or

```php
#[ORM\Table(name: 'order_items')]
#[ORM\Entity]
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
#[ORM\Table(name: 'order_items')]
#[ORM\Entity]
#[LoggableChild(Loggable::STRATEGY_INCLUDE_ALL)]
class OrderItem
{
    ...

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Order
     */
    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
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
 * @method \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTranslation translation(?string $locale = null)
 */
#[ORM\Table(name: 'order_statuses')]
#[ORM\Entity]
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

## Recording why a change was made

The change set says what changed, but not why it changed.
When the reason matters for auditing, register a note for the entity before the change is flushed and the logs created by that flush will carry it in the `note` column.

```php
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogNoteRegistry;

class ProductReviewFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly EntityLogNoteRegistry $entityLogNoteRegistry,
    ) {
    }

    public function edit(ProductReview $productReview, ProductReviewData $productReviewData): void
    {
        if ($productReviewData->contentChangeReason !== null) {
            $this->entityLogNoteRegistry->registerNote($productReview, $productReviewData->contentChangeReason);
        }

        $productReview->edit($productReviewData);

        $this->em->flush();
    }
}
```

!!! note

    The registry is emptied after every flush, so the note has to be registered right before the flush that performs the change.
    Do not detach or clear the entity manager in between either — a re-fetched entity is a different object and its note would no longer be found.

### How many records the note ends up in

Doctrine reports an update once per changed entity, not once per changed property, and one log record is created for each such report.
Changing several properties of one entity therefore produces a single record holding the whole change set, and the note is stored on it exactly once.
This includes properties behind an association — a changed `$product` is just another entry in the change set of the same record, not a record of its own.

A flush that changes more entities is different.
Every changed entity — a `LoggableChild` entity, or an entity reached through a logged collection — gets its own record, and all records of that flush share a `logCollectionNumber`.

## List of results

The administration displays the logged changes as a timeline rendered by the `Admin:EntityLogTimeline` Twig component (`Shopsys\AdministrationBundle\Component\EntityLog\Timeline\TwigComponent\EntityLogTimelineComponent`).
You can render the timeline in any administration template by passing the logged entity name and its ID:

```twig
{{ component('Admin:EntityLogTimeline', {
    entityName: entityName,
    entityId: entityId,
}) }}
```

The `entityName` is the short name of the entity used in the logs.
You can get it from the `Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade::getEntityNameByEntity()` method, which accepts both an entity instance and a class name:

```php
$entityName = $this->entityLogFacade->getEntityNameByEntity($order);
```

The timeline groups records from the same save operation by `logCollectionNumber` and displays the details needed for reviewing changes: action, entity, readable identifier, user, date, note, and formatted changes.

As an implemented sample, you can study the "History" tab on the order detail page in the administration.
The tab is provided by the live component `Shopsys\AdministrationBundle\Component\OrderDetail\LiveComponent\HistoryTabComponent`, which resolves the entity name via `EntityLogFacade` and renders the timeline for the displayed order.
