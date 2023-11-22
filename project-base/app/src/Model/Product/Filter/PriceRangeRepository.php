<?php

declare(strict_types=1);

namespace App\Model\Product\Filter;

use App\Component\Doctrine\QueryBuilderExtender;
use App\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Filter\PriceRangeRepository as BasePriceRangeRepository;

/**
 * @property \App\Component\Doctrine\QueryBuilderExtender $queryBuilderExtender
 * @property \App\Model\Product\ProductRepository $productRepository
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange getPriceRangeForBrand(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, \App\Model\Product\Brand\Brand $brand)
 */
class PriceRangeRepository extends BasePriceRangeRepository
{
    /**
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \App\Component\Doctrine\QueryBuilderExtender $queryBuilderExtender
     */
    public function __construct(
        ProductRepository $productRepository,
        QueryBuilderExtender $queryBuilderExtender,
    ) {
        parent::__construct($productRepository, $queryBuilderExtender);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param \App\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
     */
    public function getPriceRangeInCategory($domainId, PricingGroup $pricingGroup, Category $category): \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
    {
        $productsQueryBuilder = $this->productRepository->getSellableInCategoryQueryBuilder(
            $domainId,
            $pricingGroup,
            $category,
        );

        return $this->getPriceRangeByProductsQueryBuilder($productsQueryBuilder, $pricingGroup);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param string $locale
     * @param string|null $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
     */
    public function getPriceRangeForSearch($domainId, PricingGroup $pricingGroup, $locale, $searchText): \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
    {
        $productsQueryBuilder = $this->productRepository
            ->getSellableBySearchTextQueryBuilder($domainId, $pricingGroup, $locale, $searchText);

        return $this->getPriceRangeByProductsQueryBuilder($productsQueryBuilder, $pricingGroup);
    }
}
