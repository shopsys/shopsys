<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Producer;

use Cdn77\RabbitMQBundle\RabbitMQ\Connection;
use Cdn77\RabbitMQBundle\RabbitMQ\Message;
use Cdn77\RabbitMQBundle\RabbitMQ\Operation\PublishOperation;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductExportProducer
{
    /**
     * @var \Cdn77\RabbitMQBundle\RabbitMQ\Connection
     */
    protected $connection;

    /**
     * @var \Cdn77\RabbitMQBundle\RabbitMQ\Operation\PublishOperation
     */
    protected $publishOperation;

    /**
     * @param \Cdn77\RabbitMQBundle\RabbitMQ\Connection $connection
     * @param \Cdn77\RabbitMQBundle\RabbitMQ\Operation\PublishOperation $publishOperation
     */
    public function __construct(Connection $connection, PublishOperation $publishOperation)
    {
        $this->connection = $connection;
        $this->publishOperation = $publishOperation;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param string $routingKey
     */
    public function publishProduct(Product $product, string $routingKey = ''): void
    {
        $message = Message::json(
            json_encode(
                [
                    'product_id' => $product->getId(),
                ]
            ),
            [
                'Application-Headers' => [
                    'x-delay' => 100,
                ],
            ]
        );

        $this->publishOperation->handle(
            $this->connection,
            $message,
            $routingKey,
            'products_export'
        );
    }
}
