<?php

declare(strict_types=1);

namespace App\Model\Product\Brand;

use App\Component\Doctrine\OrderByCollationHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
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
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        private Domain $domain,
    ) {
        parent::__construct($entityManager);
    }

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
            ->orderBy(OrderByCollationHelper::createOrderByForLocale('b.name', $this->domain->getLocale()), 'asc');

        return $brandsQueryBuilder->getQuery()->getResult();
    }
}
