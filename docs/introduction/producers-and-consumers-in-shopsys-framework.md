# Producers and Consumers in Shopsys Framework

Producers and consumers help to make [Asynchronous tasks with RabbitMQ](./asynchronous-tasks-with-rabbitmq.md) possible.

In Shopsys Framework, we use [RabbitMQBundle](https://github.com/php-amqplib/RabbitMqBundle) library to handle sending and processing messages.

All producers and consumers can be registered in `old_sound_rabbit_mq.yml` file in your project or, if they're part of the framework, in `FrameworkBundle`.

## Producers

### `Shopsys\FrameworkBundle\Model\Product\ProductChangeMessageProducer`

This producer works like the adapter for the `product_change` producer, registered in `old_sound_rabbit_mq.yml` file in `FrameworkBundle`.
That means it takes the original RabbitMQBundle's producer as the argument of the constructor and introduce own, much clearer interface to produce messages about changed products.

It also allows us to incorporate another logic in producer, for example changes in variant should propagate change in main variant too.

To work properly, the producer is registered in `services.yml` file in `FrameworkBundle` as

```yaml
Shopsys\FrameworkBundle\Model\Product\ProductChangeMessageProducer:
    arguments:
        - '@old_sound_rabbit_mq.product_change_producer'
```

_Note: The original `product_change` producer should NOT be used directly in the application._

## Consumers

### `Shopsys\FrameworkBundle\Model\Product\Visibility\ProductVisibilityRecalculateConsumerInterface`

In `FrameworkBundle` already exists the implementation of this interface – `Shopsys\FrameworkBundle\Model\Product\Visibility\ProductVisibilityRecalculateConsumer`.

This consumer is registered in `old_sound_rabbit_mq.yml` file in `FrameworkBundle`.

Consumer is responsible for [product visibility](../functional/product-visibility-and-exclude-from-sale.md) recalculations based on the other relevant product data and accepts Product ID in the message body.
