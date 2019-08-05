<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ProductChangeMessageProducer
{
    /**
     * @var int[]
     */
    protected $scheduledProductIds = [];

    /**
     * @var \OldSound\RabbitMqBundle\RabbitMq\ProducerInterface
     */
    protected $productChangeProducer;

    /**
     * @param \OldSound\RabbitMqBundle\RabbitMq\ProducerInterface $productChangeProducer
     */
    public function __construct(ProducerInterface $productChangeProducer)
    {
        $this->productChangeProducer = $productChangeProducer;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    public function productChanged(Product $product): void
    {
        $this->productsChangedByIds($this->getAllAssociatedProductIds($product));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    public function productsChanged(array $products): void
    {
        $productIdsToBeChanged = [];

        foreach ($products as $product) {
            $productIdsToBeChanged = array_merge($productIdsToBeChanged, $this->getAllAssociatedProductIds($product));
        }

        $this->productsChangedByIds($productIdsToBeChanged);
    }

    /**
     * @param int $productId
     */
    public function productChangedById(int $productId): void
    {
        if (!in_array($productId, $this->scheduledProductIds, true)) {
            $this->scheduledProductIds[] = $productId;
        }
    }

    /**
     * @param int[] $productIds
     */
    public function productsChangedByIds(array $productIds): void
    {
        $productIds = array_unique($productIds);

        foreach ($productIds as $productId) {
            $this->productChangedById($productId);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return int[]
     */
    protected function getAllAssociatedProductIds(Product $product): array
    {
        $productIds = [$product->getId()];

        if ($product->isMainVariant()) {
            foreach ($product->getVariants() as $variant) {
                $productIds[] = $variant->getId();
            }
        } elseif ($product->isVariant()) {
            $mainVariant = $product->getMainVariant();
            $productIds[] = $mainVariant->getId();
        }

        return $productIds;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        foreach ($this->scheduledProductIds as $productId) {
            $this->productChangeProducer->publish($productId);
        }
    }
}
