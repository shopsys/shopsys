<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search\Export;

use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\DequeuerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Webmozart\Assert\Assert;

class ProductSearchExportConsumer implements ProductSearchExportConsumerInterface, ConsumerInterface
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportFacade
     */
    protected $productSearchExportFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportFacade $productSearchExportFacade
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ProductSearchExportFacade $productSearchExportFacade
    ) {
        $this->entityManager = $entityManager;
        $this->productSearchExportFacade = $productSearchExportFacade;
    }

    /**
     * @param \PhpAmqpLib\Message\AMQPMessage $message
     * @return int
     */
    public function execute(AMQPMessage $message): int
    {
        $messageBody = $message->getBody();

        if (!is_numeric($messageBody) || $messageBody != (int)$messageBody) {
            return ConsumerInterface::MSG_REJECT;
        }

        $productId = (int)$messageBody;

        $this->entityManager->clear();

        $this->productSearchExportFacade->exportIds([$productId]);

        return ConsumerInterface::MSG_ACK;
    }
}
