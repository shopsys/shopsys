<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class ProductPriceRecalculationScheduler
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    private $products = [];

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function scheduleAllProductsForDelayedRecalculation()
    {
        $this->productRepository->markAllProductsForPriceRecalculation();
    }

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult|\Shopsys\FrameworkBundle\Model\Product\Product[][]
     */
    public function getProductsIteratorForDelayedRecalculation()
    {
        return $this->productRepository->getProductsForPriceRecalculationIterator();
    }
}
