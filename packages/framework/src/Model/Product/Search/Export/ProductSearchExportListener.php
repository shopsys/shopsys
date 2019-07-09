<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search\Export;

use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\DequeuerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ProductSearchExportListener
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
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportScheduler
     */
    protected $productSearchExportScheduler;

    /**
     * @var \OldSound\RabbitMqBundle\RabbitMq\ProducerInterface
     */
    private $productReindexProducer;

    /**
     * @param \OldSound\RabbitMqBundle\RabbitMq\ProducerInterface $productReindexProducer
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportScheduler $productSearchExportScheduler
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportFacade $productSearchExportFacade
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(
        ProducerInterface $productReindexProducer,
        ProductSearchExportScheduler $productSearchExportScheduler,
        ProductSearchExportFacade $productSearchExportFacade,
        EntityManagerInterface $entityManager
    ) {
        $this->productSearchExportScheduler = $productSearchExportScheduler;
        $this->productSearchExportFacade = $productSearchExportFacade;
        $this->entityManager = $entityManager;
        $this->productReindexProducer = $productReindexProducer;
    }

    public function exportScheduledProducts(): void
    {
        if ($this->productSearchExportScheduler->hasAnyProductIdsForImmediateExport()) {
            $productIds = $this->productSearchExportScheduler->getProductIdsForImmediateExport();

            foreach ($productIds as $productId) {
                $this->productReindexProducer->publish($productId);
            }
        }
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $filterResponseEvent
     */
    public function onKernelResponse(FilterResponseEvent $filterResponseEvent): void
    {
        $this->exportScheduledProducts();
    }
}
