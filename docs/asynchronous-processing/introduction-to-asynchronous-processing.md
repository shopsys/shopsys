# Introduction to asynchronous processing

[TOC]

Asynchronous processing is a way to process a large amount of data without blocking the user interface.
It is a common technique used in web applications.
In this article, we will learn how to use RabbitMQ and how common pitfalls are solved in the Shopsys Platform.

## What technologies are used?

RabbitMQ is used as a message broker (the messages are sent using the [AMQP](https://www.amqp.org/) protocol).
You can think about it as a post office: when you put the mail that you want to post in a post-box, you can be sure that Mr. Postman will eventually deliver the mail to your recipient.

[Symfony/Messenger](https://symfony.com/doc/5.4/messenger.html) provides a message bus with the ability to send messages and then handle them immediately in your application or send them through transports into queues to be handled later.

## Configuration

The following environment variables are used to configure Symfony/Messenger:

-   `MESSENGER_TRANSPORT_DSN` – the RabbitMQ connection string in the [DSN format](https://symfony.com/doc/5.4/messenger.html#transport-configuration)
-   `MESSENGER_TRANSPORT_VHOST` – the RabbitMQ [virtual host](https://www.rabbitmq.com/vhosts.html)

The configuration of the Symfony/Messenger is in the `config/packages/messenger.yaml` file.

## Dispatching a message

Every message has its dispatcher and an appropriate dispatch method(s) – for example `Shopsys\FrameworkBundle\Model\Order\Messenger\PlacedOrderMessageDispatcher::dispatchPlacedOrderMessage(int $orderId)`.

You can use the appropriate dispatcher to send a requested message to the appropriate queue.

### When is the message really sent?

When you dispatch a message, it is not sent immediately.

The motivation is that it's not needed to think about the transactional context of the message dispatching.
The message may be safely dispatched inside the transaction and will be sent to the queue after the transaction is committed.
Also, the additional dispatch in the extended code is not needed (e.g., when a message is dispatched in the `\Shopsys\FrameworkBundle\Model\Product\ProductFacade::edit()` method, it's not necessary to dispatch the message again in the extended code even when data is modified in some way).

Thanks to the Symfony's Messenger middleware `Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope\DelayEnvelopeMiddleware`, the dispatched messages are first collected in the `Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope\DelayedEnvelopesCollector`.

Each message passed through this middleware is then stamped with the `Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope\DelayedStamp` stamp.
You can read more about message stamps in the [Symfony documentation](https://symfony.com/doc/5.4/messenger.html#envelopes-stamps).

The real sending of the messages is done in the subscriber `Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope\DispatchCollectedEnvelopesSubscriber`,
which listens to the `kernel.response`, `console.terminate`, and `Symfony\Component\Messenger\Event\WorkerMessageHandledEvent` events (e.g., when the response is sent, when the console command is finished, or when the message is processed by the worker).

#### What if I really need to dispatch a message immediately?

Message wrapped in the envelope stamped with the `Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope\DelayedStamp` stamp is sent immediately.
So, you can dispatch a message like

```php
$envelope = new Envelope(
    new MyMessage($data),
    [new DelayedEnvelopeStamp()]
);

$this->messageBus->dispatch($envelope);
```

!!! warning

     You should not use this technique unless you have a good reason.
     The message may be processed almost immediately and possibly even before the transaction is committed, furthermore the message will be processed even if the transaction is rolled back.
     All of this may lead to unexpected results and/or errors.

## Processing a message

### Clearing the entity manager

When you consume a message, the entity manager is cleared automatically thanks to the `Symfony\Bridge\Doctrine\Messenger\DoctrineClearEntityManagerWorkerSubscriber`.

That means that you can be sure that the entity manager is empty when you start processing any message.

This is important to do because messages should not be processed with any stale data created by the previous message – any data may be changed in between the messages.

### Clearing the application state

Because the consumer is a long-running process, the application is **NOT** restarted after each message.

That means that your custom-made memory caches **ARE PRESERVED** between the messages.

For example:

```php
class MyService
{
    private $cache = [];

    public function getSomething($id)
    {
        if (isset($this->cache[$id])) {
            $this->cache[$id] = $this->calculateSomething($id);
        }

        return $this->cache[$id];
    }
}
```

To solve this problem, the caches should be cleared after each message.
Everything you need to do is implement the `Symfony\Contracts\Service\ResetInterface`.

Thanks to the messenger configuration, namely the `reset_on_message: true`, the `reset()` method is called automatically after each processed message.

So the proper code could look like this:

```php
class MyService implements ResetInterface
{
    private $cache = [];

    public function getSomething($id)
    {
        if (isset($this->cache[$id])) {
            $this->cache[$id] = $this->calculateSomething($id);
        }

        return $this->cache[$id];
    }

    public function reset()
    {
        $this->cache = [];
    }
}
```

### Failure transport (dead letter queue)

When a message fails to be processed (for example, due to an exception), and a [max retry count is exceeded](https://symfony.com/doc/current/messenger.html#retries-failures), it is sent to the failure transport.
Its configuration is in the `config/packages/messenger.yaml` file.

The failure transport is a special queue where all failed messages are sent.
You can then inspect the failed messages and retry them manually.
More information is in the [Handling failed product recalculations](./handling-failed-product-recalculations.md) article.

## Available queues and their purpose

This list is not meant to be complete – it contains only the most important queues.
You can check the full list of queues in the `config/packages/messenger.yaml` file.

### product_recalculation

Handles recalculations of products (visibility recalculation and export to Elasticsearch).

More information is in the [Product recalculations](./product-recalculations.md) article.

### placed_order_transport

Should be used to react to the created order.
For example, the `Shopsys\FrameworkBundle\Model\Order\Messenger\HeurekaPlacedOrderMessageHandler` handler sends the order to the Heureka marketplace.
You can create your custom handler to send the order to the ERP system, for example, without the need to change the `Shopsys\FrameworkBundle\Model\Order\OrderFacade` class.

!!! note

    The queues are created automatically when the first message is sent to them.
    You can create all queues by calling the `./bin/console messenger:setup-transports` command.

## Consumer infrastructure

### Local development

In local development, the consumer is created as a separate docker container.
You can check the `php-consumer` container configuration in the appropriate `docker-compose*` file for your platform in the `docker/conf` folder.

The container uses entrypoint `docker/php-fpm/consumer-entrypoint.sh` (you can check what is run in the container in this file).
This container consumes messages from every queue (except the `failed` queue).

!!! note

    When you add a new transport, don't forget to add it to the `docker/php-fpm/consumer-entrypoint.sh` file, so the new queue is consumed locally.

The consumer is automatically restarted after each 60s, so your changes are applied each 60s.
Keep that in mind when you are working on the consumer's code.
You can always restart the consumer manually by running `docker-compose restart php-consumer`.

### Production usage

The following section assumes the use of the [shopsys/deployment](https://github.com/shopsys/deployment) package for deployment.
If you use your way of deployment, it still may be used as an inspiration.

RabbitMQ is deployed as a separate pod thanks to the deployment file into the Kubernetes cluster.
You can check the configuration in the [rabbitmq.yaml deployment file](https://github.com/shopsys/deployment/blob/main/kubernetes/deployments/rabbitmq.yaml).

Consumers may be easily deployed with the [default configuration](https://github.com/shopsys/deployment/blob/main/kubernetes/manifest-templates/consumer.template.yaml) in your custom [`deploy-project.sh`](https://github.com/shopsys/deployment/blob/main/docs/deploy-project.sh#L69) file.

!!! note

    In the default configuration, each consumer has set a timeout of 600 seconds (see `bin/console messenger:consume {{TRANSPORT_NAMES}} --time-limit=600 -vv` in the deployment file).<br>
    Meaning after each 5 minutes the consumer is restarted to prevent memory leaks and other problems.

```bash
# Specify consumer configuration with the default configuration in the format:
# <consumer-name>:<transport-names-separated-by-space>:<number-of-consumers>
DEFAULT_CONSUMERS=(
    "example:example_transport:1"
)
```

If you need to consume messages from new transport, you need to add it to the `DEFAULT_CONSUMERS` variable.

You have two options:

#### 1. Add transport to the existing consumer

```bash
DEFAULT_CONSUMERS=(
    "example:example_transport my_new_transport:1"
)
```

That way only one pod will be deployed, and it will consume messages from both `example_transport` and `my_new_transport` queues in the order they are specified in the configuration (meaning no messages from the `my_new_transport` will be processed until `example_transport` is empty).
For example, this is the way how product recalculation priority is implemented (you can examine `product_recalculation_priority_regular` and `product_recalculation_priority_high` transports for detailed information).

### Gitlab CI review stage

If you use Gitlab CI, a single RabbitMQ message broker is deployed for all review apps (branches).
To ensure the isolation of the review apps, each one of them has its own virtual host (see `MESSENGER_TRANSPORT_VHOST` in the `gitlab/docker-compose-ci-review.yml` file).
The vhost is automatically created when the review app is deployed thanks to `gitlab/scripts/rabbitmq-vhost.sh` that is called inside the `.gitlab-ci.yaml` config file.

#### 2. Create a new consumer

```bash
DEFAULT_CONSUMERS=(
    "example:example_transport:1"
    "example:my_new_transport:1"
)
```

That way two pods will be deployed, and each of them will consume messages independently.

You can always create a custom deployment file for your consumer if the default configuration is not enough for you.

#### Rolling out a new version (deploying changes)

When deployment is in progress, each consumer receives the "./bin/console messenger:stop-workers" as a part of its pre-stop hook.
Each consumer will finish the message they are currently processing and then exit.

Processing the message should be fast, so the consumer could be stopped as soon as possible.
But in the case of a long-running process, the consumer will be forcibly stopped after 600 seconds (see `progressDeadlineSeconds` in the deployment file).

## How to check the state of the queues?

Locally, you can use the RabbitMQ management interface on the `http://127.0.0.1:15672/` URL (the default username and password are `guest`).

## Logs and errors

Errors from consumers are by default logged in Sentry.  
You can also check the logs in the Kubernetes pod logs.

Locally, logs are available in the `php-consumer` container logs.

## Try it yourself

You can try implementing your own asynchronous notifier by following our cookbook article [Create asynchronous notifier](../cookbook/create-asynchronous-notifier.md).
