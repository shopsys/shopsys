<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\BestsellingProduct;

use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class BestsellingProductFacade
{
    protected const ORDERS_CREATED_AT_LIMIT = '-1 month';
    public const MAX_RESULTS_ADMIN = 10;

    public function __construct(
        protected readonly AutomaticBestsellingProductRepository $automaticBestsellingProductRepository,
        protected readonly ManualBestsellingProductRepository $manualBestsellingProductRepository,
        protected readonly BestsellingProductCombinator $bestsellingProductCombinator,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
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
            $this->clock->now()->modify(static::ORDERS_CREATED_AT_LIMIT),
            $limit,
        );

        return $this->bestsellingProductCombinator->combineManualAndAutomaticProducts(
            $manualProductsIndexedByPosition,
            $automaticProducts,
            $limit,
        );
    }
}
