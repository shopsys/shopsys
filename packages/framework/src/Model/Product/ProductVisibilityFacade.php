<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Category\Category;

class ProductVisibilityFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository
     */
    protected $productVisibilityRepository;

    /**
     * @var bool
     */
    protected $recalcVisibilityForMarked = false;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductChangeMessageProducer
     */
    protected $productChangeMessageProducer;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository $productVisibilityRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductChangeMessageProducer $productChangeMessageProducer
     */
    public function __construct(
        ProductVisibilityRepository $productVisibilityRepository,
        ProductChangeMessageProducer $productChangeMessageProducer
    ) {
        $this->productVisibilityRepository = $productVisibilityRepository;
        $this->productChangeMessageProducer = $productChangeMessageProducer;
    }

    public function refreshProductsVisibility(): void
    {
        $this->productVisibilityRepository->refreshProductsVisibility();
    }

    /**
     * @param int $productId
     */
    public function refreshProductVisibilityById(int $productId): void
    {
        $this->productVisibilityRepository->refreshProductsVisibility($productId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     */
    public function refreshProductsVisibilityByCategory(Category $category): void
    {
        $productIds = $this->productVisibilityRepository->getProductIdsForRecalculationAffectedByCategory($category);
        $this->productChangeMessageProducer->productsChangedByIds($productIds);
    }
}
