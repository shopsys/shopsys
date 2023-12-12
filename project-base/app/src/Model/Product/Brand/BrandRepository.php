<?php

declare(strict_types=1);

namespace App\Model\Product\Brand;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandRepository as BaseBrandRepository;

/**
 * @method \App\Model\Product\Brand\Brand getById(int $brandId)
 * @method \App\Model\Product\Brand\Brand[] getAll()
 * @method \App\Model\Product\Brand\Brand getOneByUuid(string $uuid)
 * @method \App\Model\Product\Brand\Brand[] getByUuids(string[] $uuids)
 * @method \App\Model\Product\Brand\Brand[] getBrandsByIds(int[] $brandsIds)
 */
class BrandRepository extends BaseBrandRepository
{
    /**
     * @param string $searchText
     * @return array
     */
    public function getResultsForSearch(string $searchText): array
    {
        $queryBuilder = $this->getBySearchTextQueryBuilder($searchText);
        $queryBuilder->orderBy(OrderByCollationHelper::createOrderByForLocale('b.name', $this->domain->getLocale()));

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param string|null $searchText
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getBySearchTextQueryBuilder($searchText): QueryBuilder
    {
        $queryBuilder = $this->getBrandRepository()
            ->createQueryBuilder('b')
            ->andWhere(
                'NORMALIZE(b.name) LIKE NORMALIZE(:searchText)',
            );
        $queryBuilder->setParameter('searchText', DatabaseSearching::getFullTextLikeSearchString($searchText));

        return $queryBuilder;
    }
}
