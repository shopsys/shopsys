<?php

declare(strict_types=1);

namespace App\Model\Product\Filter;

use App\Component\Doctrine\QueryBuilderExtender;
use App\Model\Product\ProductRepository;
use Override;
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

    #[Override]
    public function getPriceRangeForSearch(
        int $domainId,
        PricingGroup $pricingGroup,
        string $locale,
        ?string $searchText,
    ): PriceRange {
        $productsQueryBuilder = $this->productRepository
            ->getSellableBySearchTextQueryBuilder($domainId, $pricingGroup, $locale, $searchText);

        return $this->getPriceRangeByProductsQueryBuilder($productsQueryBuilder, $pricingGroup);
    }
}
