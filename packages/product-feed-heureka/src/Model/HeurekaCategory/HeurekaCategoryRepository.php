<?php

namespace Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderService;

class HeurekaCategoryRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderService
     */
    protected $queryBuilderService;

    public function __construct(
        EntityManagerInterface $em,
        QueryBuilderService $queryBuilderService
    ) {
        $this->em = $em;
        $this->queryBuilderService = $queryBuilderService;
    }

    protected function getHeurekaCategoryRepository(): \Doctrine\ORM\EntityRepository
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

    /**
     * @param int $categoryId
     */
    public function findByCategoryId($categoryId): ?\Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategory
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

    /**
     * @param int $id
     */
    public function getOneById($id): \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategory
    {
        $queryBuilder = $this->getHeurekaCategoryRepository()
            ->createQueryBuilder('hc')
            ->andWhere('hc.id = :id')
            ->setParameter('id', $id);
        $heurekaCategory = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($heurekaCategory === null) {
            throw new HeurekaCategoryNotFoundException(
                'Heureka category with ID ' . $id . ' does not exist.'
            );
        }

        return $heurekaCategory;
    }
}
