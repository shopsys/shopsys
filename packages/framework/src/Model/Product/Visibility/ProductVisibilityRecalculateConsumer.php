<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Visibility;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\DequeuerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class ProductVisibilityRecalculateConsumer implements ProductVisibilityRecalculateConsumerInterface, ConsumerInterface
{
    /**
     * @var \OldSound\RabbitMqBundle\RabbitMq\DequeuerInterface
     */
    protected $dequeuer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade
     */
    protected $productVisibilityFacade;

    /**
     * @param \OldSound\RabbitMqBundle\RabbitMq\DequeuerInterface $dequeuer
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     */
    public function __construct(DequeuerInterface $dequeuer, ProductVisibilityFacade $productVisibilityFacade)
    {
        $this->dequeuer = $dequeuer;
        $this->productVisibilityFacade = $productVisibilityFacade;
    }

    /**
     * @param \PhpAmqpLib\Message\AMQPMessage $message
     * @return int
     */
    public function execute(AMQPMessage $message)
    {
        $productId = (int)$message->getBody();

        $this->productVisibilityFacade->refreshProductVisibilityById($productId);

        return ConsumerInterface::MSG_ACK;
    }
}
