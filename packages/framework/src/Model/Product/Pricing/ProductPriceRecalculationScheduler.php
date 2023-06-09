<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class ProductPriceRecalculationScheduler
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    protected array $products = [];

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     */
    public function __construct(protected readonly ProductRepository $productRepository)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    public function scheduleProductForImmediateRecalculation(Product $product)
    {
        $this->products[$product->getId()] = $product;
    }

    public function scheduleAllProductsForDelayedRecalculation()
    {
        $this->productRepository->markAllProductsForPriceRecalculation();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProductsForImmediateRecalculation()
    {
        return $this->products;
    }

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult|\Shopsys\FrameworkBundle\Model\Product\Product[][]
     */
    public function getProductsIteratorForDelayedRecalculation()
    {
        return $this->productRepository->getProductsForPriceRecalculationIterator();
    }

    public function cleanScheduleForImmediateRecalculation()
    {
        $this->products = [];
    }
}
