# Order Processing

[TOC]

## Introduction

Order processing in Shopsys Platform is a critical part designed to handle the lifecycle of an order from initiation to completion.
This process involves multiple steps, orchestrated through a middleware-driven architecture to ensure flexibility and extensibility.
Below, we provide a comprehensive guide on how the order processing system works, focusing on the key elements involved.

## Key components

-   `Shopsys\FrameworkBundle\Model\Order\OrderData`
    -   This object holds all the data related to an order, including customer details, product information, pricing, and more.
-   `Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput`
    -   This object contains input information provided by the customer such as selected transport, payment methods, promo codes, and other preferences.
-   `Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor`
    -   The central engine that drives the order processing workflow by passing `Shopsys\FrameworkBundle\Model\Order\OrderData` through configured middleware.

### Middlewares

Middleware in the Shopsys Platform order processing system plays a crucial role in managing the lifecycle and transformation of an order during processing.
Each middleware is designed to perform specific tasks that modify the order data as it moves through the processing pipeline and is usually responsible for:

-   Creating Order Items
    -   Middleware may add new items to the order based on the inputs provided.
-   Adjusting Total Price
    -   Modifications to the order's total price are handled, which could include applying the price of the item, subtracting discounts, or other price adjustments.
-   Adjusting Already Set Data
    -   Middleware can modify existing data in the `OrderData` object, such as updating the customer's details, changing delivery options, or recalibrating the payment methods.

By implementing the middleware pattern, the order processing system can be easily extended and customized to meet specific business requirements.

Each middleware must implement the `OrderProcessorMiddlewareInterface`, which is defined within the Shopsys Framework.
The interface requires the middleware to have a `handle` method with the following signature:

```php
/**
 * Method is responsible for updating the OrderProcessingData object and call the next middleware in the stack
 *
 * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData $orderProcessingData
 * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack $orderProcessingStack
 * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData
 */
public function handle(
    OrderProcessingData $orderProcessingData,
    OrderProcessingStack $orderProcessingStack,
): OrderProcessingData;
```

**OrderProcessingData**  
This parameter is an object that contains both the `OrderData` and `OrderInput`, encapsulating all current order information and input specifics for streamlined processing.
This object is created automatically by the `OrderProcessor` and passed to each middleware for processing.

**OrderProcessingStack**  
This represents the stack of middleware yet to be executed.
It allows the current middleware to pass control to the next middleware once its processing is complete.

The typical flow within the handle method involves reading and modifying `OrderData` – The middleware performs its designated operations by manipulating the `OrderProcessingData->orderData`.
The `handle()` method is then responsible for continuing the middleware chain – Once the middleware completes its task (in specific cases even during the processing), it must call the next middleware in the stack:

```php
$orderProcessingStack->processNext($orderProcessingData);
```

This line of code ensures that the `OrderProcessingData` object is passed to the next middleware for further processing, maintaining the integrity of the processing chain.

### Examples of some default middlewares

Each middleware interacts with the `OrderData`, potentially modifying it based on the `OrderInput` and the specific logic defined within the middleware.

The following are some middlewares involved in the order processing system (this list is not meant to be exhaustive):

-   `SetCustomerUserMiddleware`: Assigns customer user details to the order.
-   `AddProductsMiddleware`: Adds requested products to the order.
-   `ApplyPercentagePromoCodeMiddleware`: Applies percentage-based discounts through promo codes.
-   `AddTransportMiddleware`: Adds transportation details to the order.
-   `AddPaymentMiddleware`: Adds payment details to the order.
-   `PersonalPickupPointMiddleware`: Configures order pickup details if personal pickup is selected.
-   `AddRoundingMiddleware`: Applies rounding rules to the order total price.

### Configuring the middleware processing stack

In Shopsys Platform, the order processing system relies on a series of middleware to handle various aspects of order manipulation and processing.
Configuring these middlewares requires defining them in a specific order in the configuration file `app/config/packages/shopsys_framework.yaml`.

Example of the configuration file:

```yaml
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
            - 'App\Model\Order\Processing\OrderProcessorMiddleware\SetOrderDataAsAdministratorMiddleware'
            - 'Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\SetDeliveryAddressByDeliveryAddressUuidMiddleware'
```

Under the `shopsys_framework.order.processing_middlewares` key configures the middleware sequence – each middleware is listed in the order they should be executed.
This sequence is critical as each middleware may depend on the modifications made by the previous one.

Middlewares are referenced by their fully qualified class names, ensuring that the application can correctly locate and create an appropriate Symfony service.

To customize the order processing, you may need to add or remove middleware from this stack based on your specific requirements.

!!! note "Best practices"

    Any changes to the middleware stack should be thoroughly tested in a development environment to avoid disrupting order processing in production.<br>
    Document any custom middleware and changes to the processing stack within your team or project documentation to maintain clarity on how order processing is configured.

## Create order programmatically

Let's assume we have a product, transport, and payment method that we want to use to create an order programmatically.

```php
$product = $this->productRepository->getById(1);
$transport = $this->transportRepository->getById(3);
$payment = $this->paymentRepository->getById(1);
```

First, we need to create an instance of the `OrderInput` object, which encapsulates the order input data and populate the product, payment, and transport.

```php
// orderInputFactory is an instance of \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory
$orderInput = $this->orderInputFactory->create($this->domain->getDomainConfigById($domainId));

$orderInput->addProduct($product, 1); // 1 is the desired quantity
$orderInput->setTransport($transport);
$orderInput->setPayment($payment);
```

It is possible to set additional data (without a specific method) to the `OrderInput` object using the `addAdditionalData()` method.
Usually this data is used by custom middlewares.

```php
$orderInput->addAdditionalData(MyCustomMiddleware::DATA_KEY, $value);
```

The data may be retrieved in the middleware from the `OrderInput` object using the `findAdditionalData()` method.

```php
$value = $orderProcessingData->orderInput->findAdditionalData(MyCustomMiddleware::DATA_KEY);
```

Next, we create an instance of the `OrderData` object, with basic information.
These values are usually provided by the customer and may be further used/changed by the middlewares.

```php
$orderData = $this->orderDataFactory->create();
$orderData->firstName = 'firstName';
$orderData->lastName = 'lastName';
$orderData->email = 'email@example.com';
$orderData->telephone = '123456879';
$orderData->street = 'street';
$orderData->city = 'city';
$orderData->postcode = '12345';
$orderData->country = $this->countryFacade->findByCode('CZ');
$orderData->currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
```

Now we are ready to process the order using the `OrderProcessor` service (instance of the`\Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor` class).

```php
$orderData = $this->orderProcessor->process(
    $orderInput,
    $orderData,
);
```

After processing, the order data is ready to be used to create an order.

```php
// placeOrderFacade is an instance of \Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade class
$this->placeOrderFacade->placeOrder($orderData);
```

!!! note

    You may also call the `PlaceOrderFacade::createOrderOnly` method.<br>
    This method bypass some steps during the normal place order process, like sending emails, dispatching amqp messages, etc.<br>
    It is useful when you need to create an order without side effects - for example in tests, during imports, etc.

## Conclusion

The order processing system in Shopsys Platform is designed to be robust and adaptable, suitable for handling various e-commerce scenarios.
By leveraging a middleware approach, the platform ensures that each aspect of the order can be independently managed and updated as business needs evolve.

For more detailed instructions on adding a new order item type and integrating it with the order processing system, please refer to the [Adding a New Order Item Type cookbook](../cookbook/adding-a-new-order-item-type.md).
