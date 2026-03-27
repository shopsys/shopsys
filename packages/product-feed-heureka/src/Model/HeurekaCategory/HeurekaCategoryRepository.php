<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class HeurekaCategoryRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getHeurekaCategoryRepository(): EntityRepository
    {
        return $this->em->getRepository(HeurekaCategory::class);
    }

    /**
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategory[]
     */
    public function getAllIndexedByHeurekaId(string $locale): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('hc')
            ->from(HeurekaCategory::class, 'hc', 'hc.heurekaId')
            ->where('hc.locale = :locale')
            ->setParameter('locale', $locale);

        return $queryBuilder->getQuery()
            ->getResult();
    }

    public function findByCategoryIdAndLocale(int $categoryId, string $locale): ?HeurekaCategory
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('hc')
            ->from(HeurekaCategory::class, 'hc')
            ->join('hc.categories', 'hcc')
            ->andWhere('hcc = :categoryId')
            ->andWhere('hc.locale = :locale')
            ->setParameter('categoryId', $categoryId)
            ->setParameter('locale', $locale);

        return $queryBuilder->getQuery()
            ->getOneOrNullResult();
    }

    public function getOneByHeurekaIdAndLocale(int $heurekaId, string $locale): HeurekaCategory
    {
        $queryBuilder = $this->getHeurekaCategoryRepository()
            ->createQueryBuilder('hc')
            ->andWhere('hc.heurekaId = :heurekaId')
            ->andWhere('hc.locale = :locale')
            ->setParameter('heurekaId', $heurekaId)
            ->setParameter('locale', $locale);
        $heurekaCategory = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($heurekaCategory === null) {
            throw new HeurekaCategoryNotFoundException(
                'Heureka category with ID ' . $heurekaId . ' and locale ' . $locale . ' does not exist.',
            );
        }

        return $heurekaCategory;
    }
}
