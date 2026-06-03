<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ZboziCategoryRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getZboziCategoryRepository(): EntityRepository
    {
        return $this->em->getRepository(ZboziCategory::class);
    }

    /**
     * @return \Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategory[]
     */
    public function getAllIndexedByZboziId(string $locale): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('zc')
            ->from(ZboziCategory::class, 'zc', 'zc.zboziId')
            ->where('zc.locale = :locale')
            ->setParameter('locale', $locale);

        return $queryBuilder->getQuery()
            ->getResult();
    }

    public function findByCategoryIdAndLocale(int $categoryId, string $locale): ?ZboziCategory
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('zc')
            ->from(ZboziCategory::class, 'zc')
            ->join('zc.categories', 'zcc')
            ->andWhere('zcc = :categoryId')
            ->andWhere('zc.locale = :locale')
            ->setParameter('categoryId', $categoryId)
            ->setParameter('locale', $locale);

        return $queryBuilder->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int[] $categoryIds
     * @return array<int, string>
     */
    public function getFullNamesByCategoryIdsIndexedByCategoryId(array $categoryIds, string $locale): array
    {
        if ($categoryIds === []) {
            return [];
        }

        $rows = $this->em->createQueryBuilder()
            ->select('zcc.id AS categoryId, zc.fullName AS zboziCategoryFullName')
            ->from(ZboziCategory::class, 'zc')
            ->join('zc.categories', 'zcc')
            ->andWhere('zcc.id IN (:categoryIds)')
            ->andWhere('zc.locale = :locale')
            ->setParameter('categoryIds', $categoryIds)
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getScalarResult();

        $fullNamesByCategoryId = [];

        foreach ($rows as $row) {
            if ($row['zboziCategoryFullName'] !== null) {
                $fullNamesByCategoryId[(int)$row['categoryId']] = $row['zboziCategoryFullName'];
            }
        }

        return $fullNamesByCategoryId;
    }

    public function getOneByZboziIdAndLocale(int $zboziId, string $locale): ZboziCategory
    {
        $queryBuilder = $this->getZboziCategoryRepository()
            ->createQueryBuilder('zc')
            ->andWhere('zc.zboziId = :zboziId')
            ->andWhere('zc.locale = :locale')
            ->setParameter('zboziId', $zboziId)
            ->setParameter('locale', $locale);
        $zboziCategory = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($zboziCategory === null) {
            throw new ZboziCategoryNotFoundException(
                'Zbozi category with ID ' . $zboziId . ' and locale ' . $locale . ' does not exist.',
            );
        }

        return $zboziCategory;
    }
}
