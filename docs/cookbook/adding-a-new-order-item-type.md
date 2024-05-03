# Adding a New Order Item Type

In this cookbook, we will add a new order item type, namely an additional service.
We will see how to create a new order item type, how to add it to the administration, and how to handle it in the order process.

[TOC]

## Prerequisites

First, we need to create a new entity along with the Repository and Facade classes for the new order item type.
Below, you can see the code for those classes.  
For more information on how to create a new entity, see the [Adding a New Entity](adding-a-new-entity.md) cookbook.

<details>
<summary>Entity <code>app/Model/Service/Service.php</code></summary>

```php
namespace App\Model\Service;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

/**
 * @ORM\Table(name="services")
 * @ORM\Entity
 */
class Service
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected int $id;

    /**
     * @ORM\Column(type="guid", unique=true)
     */
    protected string $uuid;

    /**
     * @ORM\Column(type="string")
     */
    protected string $name;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    protected Money $priceWithVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    protected Money $priceWithoutVat;

    /**
     * @param string $name
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     * @param string|null $uuid
     */
    public function __construct(string $name, Price $price, string $uuid = null)
    {
        $this->name = $name;
        $this->uuid = $uuid ?? Uuid::uuid4()->toString();
        $this->priceWithVat = $price->getPriceWithVat();
        $this->priceWithoutVat = $price->getPriceWithoutVat();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getPrice(): Price
    {
        return new Price($this->priceWithoutVat, $this->priceWithVat);
    }
}
```

</details>

<details>
<summary>Repository <code>app/Model/Service/ServiceRepository.php</code></summary>

```php
namespace App\Model\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ServiceRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository<\App\Model\Service\Service>
     */
    protected function getServiceRepository(): EntityRepository
    {
        return $this->em->getRepository(Service::class);
    }

    /**
     * @param string $uuid
     * @return \App\Model\Service\Service|null
     */
    public function findByUuid(string $uuid): ?Service
    {
        return $this->getServiceRepository()->findOneBy(['uuid' => $uuid]);
    }
}
```

</details>

<details>
<summary>Facade <code>app/Model/Service/ServiceFacade.php</code></summary>

```php
namespace App\Model\Service;

use Doctrine\ORM\EntityManagerInterface;

class ServiceFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Service\ServiceRepository $serviceRepository
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ServiceRepository $serviceRepository,
    ) {
    }

    /**
     * @param string $uuid
     * @return \App\Model\Service\Service|null
     */
    public function findByUuid(string $uuid): ?Service
    {
        return $this->serviceRepository->findByUuid($uuid);
    }
}
```

</details>

<details><summary>DataFixture <code>app/src/DataFixtures/Demo/ServiceDataFixture.php</code></summary>

```php
namespace App\DataFixtures\Demo;

use App\Model\Service\Service;
use App\Model\Service\ServiceFacade;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class ServiceDataFixture extends AbstractReferenceFixture
{
    public const string SERVICE_UUID = 'eeeccc67-c9ce-4736-bdb5-b3a1ba9fb23d';

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $servicePrice = new Price(Money::create(100), Money::create(121));
        $service = new Service('Extended warranty', $servicePrice, self::SERVICE_UUID);

        $this->em->persist($service);
        $this->em->flush();
    }
}
```

</details>

<br>

## New order item type

The first step to creating a new order item type is creating an enum constant in the `OrderItemTypeEnum` class in `app/src/Model/Order/Item`.
Thanks to the [`AbstractEnum`](../introduction/faq-and-common-issues.md#why-i-see-enum-classes-in-the-framework-that-are-not-actually-php-enums) class the new type will be automatically added to the list of available order item types.

You may also specify the sorting order of the new type with the `SORTED_TYPES` constant.
This constant defines the order in which the order item types are created and then displayed in the administration and on the order detail page.  
If you don't specify the sorting order, the new type will be added to the end of the list.

```php
namespace App\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum as BaseOrderItemTypeEnum;

class OrderItemTypeEnum extends BaseOrderItemTypeEnum
{
    public const string TYPE_SERVICE = 'service';

    protected const array SORTED_TYPES = [
        self::TYPE_PRODUCT,
        self::TYPE_SERVICE,
        self::TYPE_PAYMENT,
        self::TYPE_TRANSPORT,
        self::TYPE_DISCOUNT,
        self::TYPE_ROUNDING,
    ];
}
```

Remember to alias the base `OrderItemTypeEnum` in the `services.yaml` file.

```yaml
Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum:
    alias: App\Model\Order\Item\OrderItemTypeEnum
```

### \[optional\] enhance OrderItem class with new methods

You may add a few useful methods in a new class extending `OrderItem` in `app/src/Model/Order/Item`.
Those methods may not be necessary for all new order item types, but they are good to have for consistency and future improvements.

```php
namespace App\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem as BaseOrderItem;

class OrderItem extends BaseOrderItem
{
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

## Use the new order item type

Let's hook our new type into the order process.
We now assume that the service will be provided by the `Cart` class.
The proper implementation of the service addition to the cart is out of the scope of this cookbook.
For the sake of simplicity, we will add a new method to the `Cart` class that will always return the uuid of the service from the demo data.

```php
namespace App\Model\Cart;

use App\DataFixtures\Demo\ServiceDataFixture;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Cart\Cart as BaseCart;

/**
 * @ORM\Table(name="carts")
 * @ORM\Entity
 */
class Cart extends BaseCart
{
    /**
     * @return string
     */
    public function getServiceUuid(): string
    {
        return ServiceDataFixture::SERVICE_UUID;
    }
}
```

### Create order processing middleware for the new order item type

Each processing middleware is responsible for a specific part of the order processing.
This one will process the services added to the cart.

Middleware classes must implement the `OrderProcessorMiddlewareInterface` interface.
This interface describes the `handle()` method that is the main entrypoint for the middleware.

The `handle()` method usually:

-   create new order items
-   modify the order data
-   change the total price of the order
-   call the next middleware in the stack.

This may be the naive implementation of the middleware that adds the service to the order:

```php
namespace App\Model\Order\Processing\OrderProcessorMiddleware;

use App\Model\Order\Item\OrderItemData;
use App\Model\Order\Item\OrderItemDataFactory;
use App\Model\Order\Item\OrderItemTypeEnum;
use App\Model\Service\Service;
use App\Model\Service\ServiceFacade;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\OrderProcessorMiddlewareInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;

class AddServiceMiddleware implements OrderProcessorMiddlewareInterface
{
    public const string SERVICE_UUID = 'service_uuid';

    /**
     * @param \App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     * @param \App\Model\Service\ServiceFacade $serviceFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     */
    public function __construct(
        private readonly OrderItemDataFactory $orderItemDataFactory,
        private readonly ServiceFacade $serviceFacade,
        private readonly VatFacade $vatFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData $orderProcessingData
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack $orderProcessingStack
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData
     */
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        $orderData = $orderProcessingData->orderData;

        $serviceUuid = $orderProcessingData->orderInput->findAdditionalData(self::SERVICE_UUID);

        if ($serviceUuid === null) {
            // silently ignore missing service and continue with the next middleware
            return $orderProcessingStack->processNext($orderProcessingData);
        }

        $service = $this->serviceFacade->findByUuid($serviceUuid);

        if ($service === null) {
            // silently ignore missing service and continue with the next middleware
            return $orderProcessingStack->processNext($orderProcessingData);
        }

        $orderItemData = $this->createServiceOrderItemData($service, $orderProcessingData->getDomainId());

        $orderData->addItem($orderItemData);
        $orderData->addTotalPrice($orderItemData->getTotalPrice(), OrderItemTypeEnum::TYPE_SERVICE);

        return $orderProcessingStack->processNext($orderProcessingData);
    }

    /**
     * @param \App\Model\Service\Service $service
     * @param int $domainId
     * @return \App\Model\Order\Item\OrderItemData
     */
    public function createServiceOrderItemData(
        Service $service,
        int $domainId,
    ): OrderItemData {
        $orderItemData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_SERVICE);

        $orderItemData->name = $service->getName();
        $orderItemData->setUnitPrice($service->getPrice());
        $orderItemData->setTotalPrice($service->getPrice());
        $orderItemData->quantity = 1;
        $orderItemData->vatPercent = $this->vatFacade->getDefaultVatForDomain($domainId)->getPercent();

        return $orderItemData;
    }
}
```

You may have noticed the way we pass the service UUID to the middleware.

```php
$serviceUuid = $orderProcessingData->orderInput->findAdditionalData(self::SERVICE_UUID);
```

The `OrderInput` class is a container for input data required for order processing.
It is created by the `OrderInputFactory` class and passed to the `OrderProcessingData` object.
The `addAdditionalData()`/`findAdditionalData()` methods are a convenient way to work with the additional data required for processing without the need to create new properties in the `OrderInput` class.
Though it is still possible to add new properties to the `OrderInput` class if needed.

### Pass the service UUID from the cart to the order input

Now we may extend the `OrderInputFactory` class to add the new service UUID to the order input data.

```php
namespace App\Model\Order\Processing;

use App\Model\Order\Processing\OrderProcessorMiddleware\AddServiceMiddleware;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory as BaseOrderInputFactory;

class OrderInputFactory extends BaseOrderInputFactory
{
    /**
     * @param \App\Model\Cart\Cart $cart
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput
     */
    public function createFromCart(Cart $cart, DomainConfig $domainConfig): OrderInput
    {
        $orderInput = parent::createFromCart($cart, $domainConfig);

        $orderInput->addAdditionalData(AddServiceMiddleware::SERVICE_UUID, $cart->getServiceUuid());

        return $orderInput;
    }
}
```

Remember to alias the base `OrderInputFactory` in the `services.yaml` file.

```yaml
Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory:
    alias: App\Model\Order\Processing\OrderInputFactory
```

### Register the new middleware in the order processing stack

The last step is to register the new middleware in the order processing stack in the `app/config/packages/shopsys_framework.yaml` file.

```diff
shopsys_framework:
    order:
        processing_middlewares:
            - 'Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\SetCustomerUserMiddleware'
            - 'App\Model\Order\Processing\OrderProcessorMiddleware\AddProductsMiddleware'
            - 'Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\ApplyPercentagePromoCodeMiddleware'
            - 'Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\ApplyNominalPromoCodeMiddleware'
            - 'Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddTransportMiddleware'
            - 'Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddPaymentMiddleware'
            - 'Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\PersonalPickupPointMiddleware'
            - 'Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddRoundingMiddleware'
+           - 'App\Model\Order\Processing\OrderProcessorMiddleware\AddServiceMiddleware'
            - 'App\Model\Order\Processing\OrderProcessorMiddleware\SetOrderDataAsAdministratorMiddleware'
            - 'Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\SetDeliveryAddressByDeliveryAddressUuidMiddleware'
```

!!! note

    The order of the middleware is important as they will be processed in the order they are defined in the configuration

## \[optional\] change the way the new OrderItem is created

Currently, when you create the order, you will see that the "Extended warranty" service is automatically added as a new order item in administration or on the order detail page.

You may need to adjust the way the new order item is created (for example, add a relation to the service entity).

To do this, you need to extend the `OrderItemFactory` class :

```php
namespace App\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory as BaseOrderItemFactory;

class OrderItemFactory extends BaseOrderItemFactory
{
    /**
     * @param \App\Model\Order\Item\OrderItemData $orderItemData
     * @param \App\Model\Order\Order $order
     * @param \App\Model\Service\Service $service
     * @return \App\Model\Order\Item\OrderItem
     */
    public function createService(
      OrderItemData $orderItemData,
      Order $order,
      Service $service,
    ): OrderItem {
        $orderItem = $this->createOrderItem(
            $orderItemData,
            $order,
        );

        // you will need to add the setService method to the OrderItem class
        $orderItem->setService($service);

        return  $orderItem;
    }
}
```

And use this new method in the `App\Model\Order\PlaceOrderFacade` class:

```php
namespace App\Model\Order;

use App\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade as BasePlaceOrderFacade;

class PlaceOrderFacade extends BasePlaceOrderFacade
{
    protected function createSpecificOrderItem(OrderItemData $orderItemData, Order $order,): OrderItem
    {
        return match ($orderItemData->type) {
            OrderItemTypeEnum::TYPE_SERVICE => $this->orderItemFactory->createService(
                $orderItemData,
                $order,
                $orderItemData->service, // you will need to add the service property to the OrderItemData class and set it in the AddServiceMiddleware
            ),
            default => parent::createSpecificOrderItem($orderItemData, $order)
        };
    }
}
```

## Conclusion

In this cookbook, we've successfully integrated a new order item type, an additional service, into the Shopsys Platform.
By following these steps, we not only added a new order item type but also ensured it integrates seamlessly with the existing order processing workflow.
This approach allows for further customizations and extensions, providing a robust foundation for managing additional services within the Shopsys Platform.
