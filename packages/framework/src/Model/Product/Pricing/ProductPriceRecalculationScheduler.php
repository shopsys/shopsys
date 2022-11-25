<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Doctrine\ORM\Internal\Hydration\IterableResult;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class ProductPriceRecalculationScheduler
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    protected $products = [];

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    public function scheduleProductForImmediateRecalculation(Product $product): void
    {
        $this->products[$product->getId()] = $product;
    }

    public function scheduleAllProductsForDelayedRecalculation(): void
    {
        $this->productRepository->markAllProductsForPriceRecalculation();
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
    public function getProductsIteratorForDelayedRecalculation(): IterableResult|array
    {
        return $this->productRepository->getProductsForPriceRecalculationIterator();
    }

    public function cleanScheduleForImmediateRecalculation(): void
    {
        $this->products = [];
    }
}
