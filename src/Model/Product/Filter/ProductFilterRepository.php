<?php

declare(strict_types=1);

namespace App\Model\Product\Filter;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
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
        if ($filterByStock) {
            $this->queryBuilderExtender->addOrExtendJoin(
                $productsQueryBuilder,
                Availability::class,
                'a',
                'p.availability = a'
            );
            $productsQueryBuilder->andWhere('a.dispatchTime = :dispatchTime');
            $productsQueryBuilder->setParameter('dispatchTime', static::DAYS_FOR_STOCK_FILTER);
        }
    }
}
