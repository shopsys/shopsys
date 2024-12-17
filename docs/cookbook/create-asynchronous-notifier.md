# Create asynchronous notifier

This article provides step-by-step instructions on how to create a notification feature that leverages asynchronous processing.

Let's say we want to notify the registered customers about a new product that has been added to the system.

## 1. Create a message class `NewProductNotificationMessage`

This class represents a message that will be sent to the queue for further processing.

```php
// app/src/Model/Product/Notification/ProductNotificationMessage.php

declare(strict_types=1);

namespace App\Model\Product\Notification;

class ProductNotificationMessage
{
    /**
     * @param int $productId
     */
    public function __construct(
        public readonly int $productId,
    ) {
    }
}

```

Notice that we store only the product ID in the message.
This is because the message will be serialized and stored in the queue and any necessary data will be retrieved from the database when the message is processed.

## 2. Create a message dispatcher class `ProductNotificationMessageDispatcher`

This class is responsible for dispatching the message to the queue.

```php
// app/src/Model/Product/Notification/ProductNotificationMessageDispatcher.php

declare(strict_types=1);

namespace App\Model\Product\Notification;

use Shopsys\FrameworkBundle\Component\Messenger\AbstractMessageDispatcher;

class ProductRecalculationDispatcher extends AbstractMessageDispatcher
{
    /**
     * @param int $productId
     */
    public function dispatchProductId(int $productId): void
    {
        $this->messageBus->dispatch(new ProductNotificationMessage($productId));
    }
}

```

The `messageBus` dependency is injected by the parent class `AbstractMessageDispatcher` and ready at your disposal.

This class will be autoconfigured as a service, so now we can call it from the `ProductFacade` class in a method responsible for product creation.

```php
// app/src/Model/Product/ProductFacade.php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductFacade as BaseProductFacade;
// ... other use statements

class ProductFacade extends BaseProductFacade

    public function __construct(
        // ... other dependencies
        private readonly ProductNotificationMessageDispatcher $productNotificationMessageDispatcher,
    ) {
        // parent::__construct(...);
    }

    public function create(ProductData $productData)
    {
        $product = parent::create($productData);

        $this->productNotificationMessageDispatcher->dispatchProductId($product->getId());

        return $product;
    }
```

### 3. Symfony messenger configuration

Now we have a base for our asynchronous notification feature, but we need to configure the Symfony messenger component to know about our message and how to handle it.

```yaml
# app/config/packages/messenger.yaml

framework:
    messenger:
        # ... other configuration
        transports:
            # ... other transports
            product_notification_transport:
                dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                options:
                    vhost: '%env(MESSENGER_TRANSPORT_VHOST)%'
                    exchange:
                        name: product_notification
                        type: direct
                    queues:
                        product_notification: ~

        routing:
            # ... other routing
            App\Model\Product\Notification\ProductNotificationMessage: product_notification_transport
```

Now we have transport called `product_notification_transport` that is configured to use the RabbitMQ queue called `product_notification`.
The message of type `ProductNotificationMessage` will be sent to this queue thanks to the routing configuration.

In the other words, anytime we call the `ProductNotificationMessageDispatcher::dispatchProductId()` method, the message will be sent to the RabbitMQ queue.

### 4. Create a message handler class `ProductNotificationMessageHandler`

Now we need to create a class that will handle the message from the queue.
This class will be autoconfigured as a service, so we can inject any dependencies we need.

This class will do the heavy lifting of the notification feature – it will retrieve the data it needs and send the notification to the customers.
Notice that the `__invoke()` gets the message as an argument, so anything we sent in the message will be available in the handler.

```php
// app/src/Model/Product/Notification/ProductNotificationMessageHandler.php

declare(strict_types=1);

namespace App\Model\Product\Notification;

use App\Model\Component\Notification\CustomerNotificationFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProductNotificationMessageHandler
{
    public function __construct(
        protected readonly ProductFacade $productFacade,
        protected readonly CustomerNotificationFacade $customerNotificationFacade,
    ) {
    }

    public function __invoke(ProductNotificationMessage $productNotificationMessage): void
    {
        $product = $this->productFacade->getById($productNotificationMessage->productId);

        $this->customerNotificationFacade->sendNotificationToRegisteredCustomers($product);
    }
}
```

!!! note

    Thanks to the `#[AsMessageHandler]` attribute the class will be autoconfigured as a message handler.

The exact implementation of the `CustomerNotificationFacade` is not important for this article.
Let's presume the `sendNotificationToRegisteredCustomers()` method accepts the product and sends email to all registered customers.

Now we can create a new product in the administration, and run the consumer that will process the messages from the queue and test our feature.

```bash
# run inside the docker container

php bin/console messenger:consume product_notification_transport
```

### 5. Local consumer configuration

The previous step works, but it is not very convenient to run the consumer manually every time we want to process the messages from the queue.
We can configure the consumer to run automatically in the background by just adding a line to the `app/docker/php-fpm/consumer-entrypoint.sh` file.

```diff
    #!/bin/sh

    TIME_LIMIT=${1:-60}

    sleep 5

    while true; do
        php ./bin/console messenger:consume \
            product_recalculation_priority_high \
            product_recalculation_priority_regular \
            placed_order_transport \
            send_email_transport \
+           product_notification_transport \
            --time-limit=$TIME_LIMIT
        sleep 2
    done
```

You can notice the running container `php-consumer`.
This container is responsible for running the consumer in the background and will process messages from all transports configured in this file.
For more information, see the [Consumer infrastructure section](../asynchronous-processing/introduction-to-asynchronous-processing.md#consumer-infrastructure).

### 6. Prepare for deployment

The last step is to prepare the deployment configuration.
Thanks to the `shopsys/deployment` package, adding the new consumer is just a matter of a line in a `app/deploy/deploy-project.sh` file:

```diff
    # ... rest of the script

    function merge() {
        # Specify consumers configuration with the default configuration in the format:
        # <consumer-name>:<transport-names-separated-by-space>:<number-of-consumers>
        DEFAULT_CONSUMERS=(
            "product-recalculation:product_recalculation_priority_high product_recalculation_priority_regular:1"
            "placed_order:placed_order_transport:1"
+           "product-notification:product_notification_transport:1"
        )

        source "${BASE_PATH}/vendor/shopsys/deployment/deploy/functions.sh"
        merge_configuration
        create_consumer_manifests $DEFAULT_CONSUMERS
    }
```

Now we can deploy the project, and the new consumer will be automatically configured and started.
For more information, see the [Consumer infrastructure section](../asynchronous-processing/introduction-to-asynchronous-processing.md#consumer-infrastructure).

## Conclusion

Now we have a fully functional asynchronous notification feature that will email all registered customers when a new product is created.
Thanks to the asynchronous processing, the product creation will not be slowed down by the notification feature – it's not necessary to load the customer's data during the product creation.

For more information about the asynchronous processing, see the [Asynchronous Processing](../asynchronous-processing/index.md) and the Symfony documentation about the [Messenger component](https://symfony.com/doc/5.x/messenger.html).
