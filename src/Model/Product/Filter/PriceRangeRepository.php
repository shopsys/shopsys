<?php

declare(strict_types=1);

namespace App\Model\Product\Filter;

use App\Model\Product\ProductDomain;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange;
use Shopsys\FrameworkBundle\Model\Product\Filter\PriceRangeRepository as BasePriceRangeRepository;

class PriceRangeRepository extends BasePriceRangeRepository
{
    /**
     * @param \Doctrine\ORM\QueryBuilder $productsQueryBuilder
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     */
    protected function getPriceRangeByProductsQueryBuilder(QueryBuilder $productsQueryBuilder, PricingGroup $pricingGroup)
    {
        $queryBuilder = clone $productsQueryBuilder;

        $this->queryBuilderExtender
            ->addOrExtendJoin($queryBuilder, ProductDomain::class, 'pd', 'pd.product = p')
            ->resetDQLPart('groupBy')
            ->resetDQLPart('orderBy')
            ->select('MIN(pd.lowPriceWithVat) AS minimalPrice, MAX(pd.lowPriceWithVat) AS maximalPrice');

        $priceRangeData = $queryBuilder->getQuery()->execute();
        $priceRangeDataRow = reset($priceRangeData);

        return new PriceRange(
            Money::create($priceRangeDataRow['minimalPrice'] ?? 0),
            Money::create($priceRangeDataRow['maximalPrice'] ?? 0)
        );
    }
}
