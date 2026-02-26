<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;

class CategoryParameterRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(CategoryParameter::class);
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('cp')
            ->from(CategoryParameter::class, 'cp');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryParameter[]
     */
    public function getAllByCategory(Category $category): array
    {
        return $this->getRepository()->findBy(['category' => $category]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryParameter[]
     */
    public function getCategoryParametersByCategorySortedByPosition(Category $category): array
    {
        return $this->getQueryBuilder()
            ->join(Parameter::class, 'p', Join::WITH, 'cp.parameter = p')
            ->where('cp.category = :category')
            ->setParameter('category', $category)
            ->orderBy('cp.position')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getParametersCollapsedByCategory(Category $category): array
    {
        return $this->getQueryBuilder()
            ->select('p')
            ->join(Parameter::class, 'p', Join::WITH, 'cp.parameter = p')
            ->where('cp.category = :category')
            ->andWhere('cp.collapsed = true')
            ->setParameter('category', $category)
            ->getQuery()
            ->getResult();
    }
}
