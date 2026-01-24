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
    public function getAllIndexedById(): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('hc')
            ->from(HeurekaCategory::class, 'hc', 'hc.id');

        return $queryBuilder->getQuery()
            ->execute();
    }

    public function findByCategoryId(int $categoryId): ?HeurekaCategory
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('hc')
            ->from(HeurekaCategory::class, 'hc')
            ->join('hc.categories', 'hcc')
            ->andWhere('hcc = :categoriesId')
            ->setParameter('categoriesId', $categoryId);

        return $queryBuilder->getQuery()
            ->getOneOrNullResult();
    }

    public function getOneById(int $id): HeurekaCategory
    {
        $queryBuilder = $this->getHeurekaCategoryRepository()
            ->createQueryBuilder('hc')
            ->andWhere('hc.id = :id')
            ->setParameter('id', $id);
        $heurekaCategory = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($heurekaCategory === null) {
            throw new HeurekaCategoryNotFoundException(
                'Heureka category with ID ' . $id . ' does not exist.',
            );
        }

        return $heurekaCategory;
    }
}
