<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category\TopCategory;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;

class TopCategoryRepository
{
    protected EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryRepository $categoryRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        protected readonly CategoryRepository $categoryRepository,
    ) {
        $this->em = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getTopCategoryRepository()
    {
        return $this->em->getRepository(TopCategory::class);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategory[]
     */
    public function getAllByDomainId($domainId)
    {
        return $this->getTopCategoryRepository()->findBy(['domainId' => $domainId], ['position' => 'ASC']);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategory[]
     */
    public function getVisibleByDomainId($domainId)
    {
        return $this->categoryRepository->getAllVisibleByDomainIdQueryBuilder($domainId)
            ->select('tc')
            ->join(TopCategory::class, 'tc', Join::WITH, 'tc.category = c AND tc.domainId = cd.domainId')
            ->orderBy('tc.position')
            ->getQuery()
            ->getResult();
    }
}
