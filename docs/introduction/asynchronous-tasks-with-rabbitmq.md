# Asynchronous tasks with RabbitMQ

[RabbitMQ](https://www.rabbitmq.com/getstarted.html) is a message broker.
It accepts, stores and forwards binary blobs of data ‒ messages.

This allows the application to communicate in a distributed way and to coordinate parallel processes.

The usage of RabbitMQ for asynchronous tasks enables us to not block the main application thread with synchronous tasks and to avoid the need to wait for a [Cron module](./cron.md) to process such tasks.

It also allows us to speed up the application with parallel processing.

## Configuration

Most basic configuration to connect to RabbitMQ is stored in `parameters.yml` file (see the [Application configuration](../installation/application-configuration.md)).

The default consumer and producer configurations required for Shopsys Framework are stored in the `src/Resources/config/old_sound_rabbit_mq.yml` file in `FrameworkBundle`.

Configuration of your custom consumers/producers should be placed into `app/config/packages/old_sound_rabbit_mq.yml` file in your project.
This configuration is slightly different for testing and development environments, so if you're updating this file, don't forget to make appropriate changes in `old_sound_rabbit_mq.yml` configuration files in `dev` and `test` sub-folders too.

You can read more about the [configuration of RabbitMqBundle](https://github.com/php-amqplib/RabbitMqBundle#usage) to understand how to configure consumers and producers exactly how you need.

_Note: To be able to use configuration from multiple sources, we use [`PrependExtensionInterface`](https://symfony.com/doc/3.4/bundles/prepend_extension.html)_

### Explanation of `PrependExtensionInterface`

By default, you can configure bundles only from your project, so we would be unable to add new consumers (producers) to the framework without BC break.

`ShopsysFrameworkExtension` implements `PrependExtensionInterface::prepend()` method so we can easily add producers and consumers without breaking our BC promise.

As this method only prepends default framework settings before the setting of your project, you can override everything by changing the desired configuration in your `old_sound_rabbit_mq.yml` configuration file.

## Running consumers

### In Docker

In `docker-compose.yml` file, there is `common_consumer_configuration` alias prepared with the consumer configuration.

With that, we can simplify each consumer configuration just to the necessary things.

```yaml
product-search-export-consumer:
    <<: *common_consumer_configuration
    container_name: shopsys-framework-product-search-export-consumer
    command: product_search_export
```

The `command` key represents what consumer should be run within this container and this name is the same as is configured in `old_sound_rabbit_mq.yml` file.

If you want to add a new consumer, you just need to copy this definition and change name of the service, `container_name` and `command`.

If you need to change the number of messages processed before the consumer is restarted, you can adjust the environment variable `CONSUMER_RUNS_BEFORE_RESTART`

```diff
product-search-export-consumer:
    <<: *common_consumer_configuration
    container_name: shopsys-framework-product-search-export-consumer
    command: product_search_export
+   environment:
+       CONSUMER_RUNS_BEFORE_RESTART: 2048
```

_Note: default number of processed messages is 1000_

### In Kubernetes

In Kubernetes, consumers are, by default, placed into the `webserver-php-fpm` pod as another container.
This is to give the consumers access to the [application filesystem](./abstract-filesystem.md).

One of consumer's container configurations, placed into `webserver-php-fpm` deployment as the another container, look like this:

```yaml
-   image: ~
    name: product-search-export-consumer
    securityContext:
        runAsUser: 33
    workingDir: /var/www/html
    command:
        - docker-php-consumer-entrypoint
    args:
        - product_search_export
    volumeMounts:
        -   name: source-codes
            mountPath: /var/www/html
        -   name: domains-urls
            mountPath: /var/www/html/app/config/domains_urls.yml
            subPath: domains_urls.yml
        -   name: parameters
            mountPath: /var/www/html/app/config/parameters.yml
            subPath: parameters.yml
    env:
        -   name: GOOGLE_CLOUD_STORAGE_BUCKET_NAME
            value: ~
        -   name: GOOGLE_CLOUD_PROJECT_ID
            value: ~
```

_Note: You should keep `php-fpm` container as the first (therefore the default one) in container list in the deployment._

Most interesting is `args` key, which represents what consumer should be run within this container and this name is the same as is configured in `old_sound_rabbit_mq.yml` file.

If you want to run another consumer, you just need to add another container with the same configuration as above and specify proper consumer in `args` field.

Other placeholder values, represented by `~`, should be updated by your build script with proper ones for this container too, so don't forget to check your build tools.

If you need to change the number of messages processed before the consumer is restarted, you can adjust the environment variable `CONSUMER_RUNS_BEFORE_RESTART`

```diff
    env:
        -   name: GOOGLE_CLOUD_STORAGE_BUCKET_NAME
            value: ~
        -   name: GOOGLE_CLOUD_PROJECT_ID
            value: ~
+       -   name: CONSUMER_RUNS_BEFORE_RESTART
+           value: 2048
```

_Note: default number of processed messages is 1000_

### Native installation / production

For natively installed application we recommend to use [Supervisor](http://supervisord.org) to ensure your consumer is up and running.

If you want to run consumers with Supervisor or add your own, please take a look at the [Native installation guide](../installation/native-installation.md#run-background-processing-with-supervisor) where you find the instructions.

By default, our configuration ensures that there is Supervisor web GUI available on [localhost:9001](http://localhost:9001/).

_Note: In production you should block port 9001 so it is not available for anybody else than system administrators._

## Consumers in development environment

Consumers are designed to start and keep processing messages until the message count threshold is reached.
This is convenient for the production environment, but whilst you're developing, it means changes in the code are not propagated into the running consumer.
To keep developer experience as smooth as possible, consumers behave slightly different when the application is running in development mode.

In Docker, the difference is made in consumer entrypoint.
In the development environment, a consumer always processes only one message and then exits.
But to prevent Docker container from restarting after each message, this repeats until the desired count of processed messages is reached and only after that the container restarts.

For natively installed application, we ensure this with the combination of Supervisor's `autorestart=true` directive and `idle_timeout` configuration of RabbitMQBundle.
Supervisor start the consumer, and when process stays up for two seconds, it's considered healthy.
After the time period specified in `idle_timeout` in RabbitMQBundle configuration, the process exits with status code specified `idle_timeout_exit_code` (should be 0 as it's expected behavior).
Supervisor starts it again with changes in code applied.

It's mandatory to have `idle_timeout` and `idle_timeout_exit_code` directives only in development environment to have production as effective as possible.

## Extensibility of consumers

Consumers can be altered in two different ways.

First, you can change its configuration in `old_sound_rabbit_mq.yml` file.
This could be useful when you need to change for example the exchange name in RabbitMQ or just configure the consumer differently.

Or the consumer can be extended, respecting the [glass-box extensibility principles](../introduction/basics-about-package-architecture.md#glass-box-extensibility).
Meaning the class of the consumer can be extended (`class MyProductSearchExportConsumer extends ProductSearchExportConsumer`) or rewritten completely (`MyProductSearchExportConsumer implements ProductSearchExportConsumerInterface, ConsumerInterface`) thanks to the interface and after registering your implementation in `services.yml` configuration file, it will be used in whole application.
You need to provide consumer service for your class, so in the end your `services.yml` should look like this:
```yml
Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportConsumerInterface:
    class: Shopsys\ShopBundle\Model\Product\Search\Export\MyProductSearchExportConsumer
```

## Extensibility of producers

Producers can be extended same way as consumers, only difference is that you need to provide producer as argument instead of consumer, so your `services.yml` should look like this:
```yml
Shopsys\FrameworkBundle\Model\Product\ProductChangeMessageProducerInterface:
    tags:
        # must be run after all recalculations
        - { name: kernel.event_listener, event: kernel.response, method: onKernelResponse, priority: -30}
    arguments:
        - '@old_sound_rabbit_mq.product_change_producer'
```
