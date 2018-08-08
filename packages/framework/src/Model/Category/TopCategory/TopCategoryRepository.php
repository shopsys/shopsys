<?php

namespace Shopsys\FrameworkBundle\Model\Category\TopCategory;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;

class TopCategoryRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryRepository
     */
    protected $categoryRepository;

    public function __construct(EntityManagerInterface $entityManager, CategoryRepository $categoryRepository)
    {
        $this->em = $entityManager;
        $this->categoryRepository = $categoryRepository;
    }

    protected function getTopCategoryRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(TopCategory::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategory[]
     */
    public function getAllByDomainId(int $domainId): array
    {
        return $this->getTopCategoryRepository()->findBy(['domainId' => $domainId], ['position' => 'ASC']);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategory[]
     */
    public function getVisibleByDomainId(int $domainId): array
    {
        return $this->categoryRepository->getAllVisibleByDomainIdQueryBuilder($domainId)
            ->select('tc')
            ->join(TopCategory::class, 'tc', Join::WITH, 'tc.category = c AND tc.domainId = cd.domainId')
            ->orderBy('tc.position')
            ->getQuery()
            ->getResult();
    }
}
