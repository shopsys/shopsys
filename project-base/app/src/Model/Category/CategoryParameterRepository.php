<?php

declare(strict_types=1);

namespace App\Model\Category;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;

class CategoryParameterRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository(): EntityRepository
    {
        return $this->em->getRepository(CategoryParameter::class);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('cp')
            ->from(CategoryParameter::class, 'cp');
    }

    /**
     * @param \App\Model\Category\Category $category
     * @return \App\Model\Category\CategoryParameter[]
     */
    public function getAllByCategory(Category $category): array
    {
        return $this->getRepository()->findBy(['category' => $category]);
    }

    /**
     * @param \App\Model\Category\Category $category
     * @return \App\Model\Category\CategoryParameter[]
     */
    public function getCategoryParametersByCategorySortedByPosition(Category $category): array
    {
        return $this->getQueryBuilder()
            ->join(Parameter::class, 'p', Join::WITH, 'cp.parameter = p')
            ->where('cp.category = :category')
            ->setParameter('category', $category)
            ->orderBy('cp.position')
            ->getQuery()
            ->execute();
    }

    /**
     * @param \App\Model\Category\Category $category
     * @return \App\Model\Product\Parameter\Parameter[]
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
            ->execute();
    }
}
