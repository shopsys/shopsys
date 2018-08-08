<?php

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class ProductAvailabilityRecalculationScheduler
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    private $products = [];

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function scheduleProductForImmediateRecalculation(Product $product): void
    {
        $this->products[$product->getId()] = $product;
    }

    public function scheduleAllProductsForDelayedRecalculation(): void
    {
        $this->productRepository->markAllProductsForAvailabilityRecalculation();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProductsForImmediateRecalculation(): array
    {
        return $this->products;
    }

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult|\Shopsys\FrameworkBundle\Model\Product\Product[][]
     */
    public function getProductsIteratorForDelayedRecalculation()
    {
        return $this->productRepository->getProductsForAvailabilityRecalculationIterator();
    }

    public function cleanScheduleForImmediateRecalculation(): void
    {
        $this->products = [];
    }
}
