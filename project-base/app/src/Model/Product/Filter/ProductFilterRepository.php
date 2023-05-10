<?php

declare(strict_types=1);

namespace App\Model\Product\Filter;

use Doctrine\ORM\QueryBuilder;
use Exception;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterRepository as BaseProductFilterRepository;

/**
 * @property \App\Component\Doctrine\QueryBuilderExtender $queryBuilderExtender
 * @method __construct(\App\Component\Doctrine\QueryBuilderExtender $queryBuilderExtender, \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterRepository $parameterFilterRepository)
 * @method filterByFlags(\Doctrine\ORM\QueryBuilder $productsQueryBuilder, \App\Model\Product\Flag\Flag[] $flags)
 * @method filterByBrands(\Doctrine\ORM\QueryBuilder $productsQueryBuilder, \App\Model\Product\Brand\Brand[] $brands)
 * @method \Doctrine\ORM\QueryBuilder getFlagsQueryBuilder(\App\Model\Product\Flag\Flag[] $flags, \Doctrine\ORM\EntityManagerInterface $em)
 * @method \Doctrine\ORM\QueryBuilder getBrandsQueryBuilder(\App\Model\Product\Brand\Brand[] $brands, \Doctrine\ORM\EntityManagerInterface $em)
 */
class ProductFilterRepository extends BaseProductFilterRepository
{
    /**
     * @param \Doctrine\ORM\QueryBuilder $productsQueryBuilder
     * @param bool $filterByStock
     */
    public function filterByStock(QueryBuilder $productsQueryBuilder, $filterByStock)
    {
        throw new Exception('Filter by Stock is deprecated');
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $productsQueryBuilder
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     */
    public function applyFiltering(
        QueryBuilder $productsQueryBuilder,
        ProductFilterData $productFilterData,
        PricingGroup $pricingGroup
    ) {
        $this->filterByPrice(
            $productsQueryBuilder,
            $productFilterData->minimalPrice,
            $productFilterData->maximalPrice,
            $pricingGroup
        );
        $this->filterByFlags(
            $productsQueryBuilder,
            $productFilterData->flags
        );
        $this->filterByBrands(
            $productsQueryBuilder,
            $productFilterData->brands
        );
        $this->parameterFilterRepository->filterByParameters(
            $productsQueryBuilder,
            $productFilterData->parameters
        );
    }
}
