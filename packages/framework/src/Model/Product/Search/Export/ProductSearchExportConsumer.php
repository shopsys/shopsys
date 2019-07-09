<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search\Export;

use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\DequeuerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class ProductSearchExportConsumer implements ConsumerInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportFacade
     */
    private $productSearchExportFacade;

    /**
     * @var \OldSound\RabbitMqBundle\RabbitMq\DequeuerInterface
     */
    private $dequeuer;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @param \OldSound\RabbitMqBundle\RabbitMq\DequeuerInterface $dequeuer
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportFacade $productSearchExportFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        DequeuerInterface $dequeuer,
        ProductSearchExportFacade $productSearchExportFacade,
        EntityManagerInterface $em
    ) {
        $this->productSearchExportFacade = $productSearchExportFacade;
        $this->dequeuer = $dequeuer;
        $this->em = $em;
    }

    /**
     * @param \PhpAmqpLib\Message\AMQPMessage $message
     * @return int
     */
    public function execute(AMQPMessage $message): int
    {
        $this->em->clear();

        $productId = (int)$message->getBody();

        echo "Reindexing product " . $productId . PHP_EOL;

        $this->productSearchExportFacade->exportIds([$productId]);

        echo "Reindexing done - " . $productId . PHP_EOL;

        return ConsumerInterface::MSG_ACK;
    }
}
