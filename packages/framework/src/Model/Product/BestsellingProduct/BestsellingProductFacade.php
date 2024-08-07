<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\BestsellingProduct;

use DateTime;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class BestsellingProductFacade
{
    protected const ORDERS_CREATED_AT_LIMIT = '-1 month';
    public const MAX_RESULTS_ADMIN = 10;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\AutomaticBestsellingProductRepository $automaticBestsellingProductRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\ManualBestsellingProductRepository $manualBestsellingProductRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\BestsellingProductCombinator $bestsellingProductCombinator
     */
    public function __construct(
        protected readonly AutomaticBestsellingProductRepository $automaticBestsellingProductRepository,
        protected readonly ManualBestsellingProductRepository $manualBestsellingProductRepository,
        protected readonly BestsellingProductCombinator $bestsellingProductCombinator,
    ) {
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getOfferedBestsellingProducts(
        int $domainId,
        Category $category,
        PricingGroup $pricingGroup,
        int $limit,
    ) {
        $manualBestsellingProducts = $this->manualBestsellingProductRepository->getOfferedByCategory(
            $domainId,
            $category,
            $pricingGroup,
        );

        $manualProductsIndexedByPosition = [];

        foreach ($manualBestsellingProducts as $manualBestsellingProduct) {
            $manualProductsIndexedByPosition[$manualBestsellingProduct->getPosition()] = $manualBestsellingProduct->getProduct();
        }

        $automaticProducts = $this->automaticBestsellingProductRepository->getOfferedProductsByCategory(
            $domainId,
            $category,
            $pricingGroup,
            new DateTime(static::ORDERS_CREATED_AT_LIMIT),
            $limit,
        );

        return $this->bestsellingProductCombinator->combineManualAndAutomaticProducts(
            $manualProductsIndexedByPosition,
            $automaticProducts,
            $limit,
        );
    }
}
