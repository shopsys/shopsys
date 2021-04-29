<?php

declare(strict_types=1);

namespace App\Model\Product\Brand;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandRepository as BaseBrandRepository;

/**
 * @method \App\Model\Product\Brand\Brand getById(int $brandId)
 * @method \App\Model\Product\Brand\Brand[] getAll()
 * @method \App\Model\Product\Brand\Brand getOneByUuid(string $uuid)
 * @method \App\Model\Product\Brand\Brand[] getByUuids(string[] $uuids)
 */
class BrandRepository extends BaseBrandRepository
{
    /**
     * @param string|null $searchText
     * @param int $page
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginationResultForSearch(
        $searchText,
        $page,
        $limit
    ): PaginationResult {
        $queryBuilder = $this->getBySearchTextQueryBuilder($searchText);
        $queryBuilder->orderBy('b.name');

        $queryPaginator = new QueryPaginator($queryBuilder);

        return $queryPaginator->getResult($page, $limit);
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
                'NORMALIZE(b.name) LIKE NORMALIZE(:searchText)'
            );
        $queryBuilder->setParameter('searchText', DatabaseSearching::getFullTextLikeSearchString($searchText));

        return $queryBuilder;
    }

    /**
     * @param int[] $brandsIds
     * @return \App\Model\Product\Brand\Brand[]
     */
    public function getBrandsByIds(array $brandsIds): array
    {
        $brandsQueryBuilder = $this->getBrandRepository()->createQueryBuilder('b')
            ->select('b')
            ->where('b.id IN (:brandIds)')
            ->setParameter('brandIds', $brandsIds)
            ->orderBy('b.name', 'asc');

        return $brandsQueryBuilder->getQuery()->getResult();
    }
}
