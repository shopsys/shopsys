<?php

declare(strict_types=1);

namespace App\Model\Product\Filter;

use App\Component\Doctrine\QueryBuilderExtender;
use App\Model\Product\ProductRepository;
use Override;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange;
use Shopsys\FrameworkBundle\Model\Product\Filter\PriceRangeRepository as BasePriceRangeRepository;

/**
 * @property \App\Component\Doctrine\QueryBuilderExtender $queryBuilderExtender
 * @property \App\Model\Product\ProductRepository $productRepository
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange getPriceRangeForBrand(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, \App\Model\Product\Brand\Brand $brand)
 */
class PriceRangeRepository extends BasePriceRangeRepository
{
    public function __construct(
        ProductRepository $productRepository,
        QueryBuilderExtender $queryBuilderExtender,
    ) {
        parent::__construct($productRepository, $queryBuilderExtender);
    }

    /**
     * @param int $domainId
     * @param \App\Model\Category\Category $category
     */
    #[Override]
    public function getPriceRangeInCategory(
        $domainId,
        PricingGroup $pricingGroup,
        Category $category,
    ): PriceRange {
        $productsQueryBuilder = $this->productRepository->getSellableInCategoryQueryBuilder(
            $domainId,
            $pricingGroup,
            $category,
        );

        return $this->getPriceRangeByProductsQueryBuilder($productsQueryBuilder, $pricingGroup);
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param string|null $searchText
     */
    #[Override]
    public function getPriceRangeForSearch(
        $domainId,
        PricingGroup $pricingGroup,
        $locale,
        $searchText,
    ): PriceRange {
        $productsQueryBuilder = $this->productRepository
            ->getSellableBySearchTextQueryBuilder($domainId, $pricingGroup, $locale, $searchText);

        return $this->getPriceRangeByProductsQueryBuilder($productsQueryBuilder, $pricingGroup);
    }
}
