<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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
        return $this->getCategoryParametersSortedByPositionIndexedByCategoryId([$category])[$category->getId()];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     * @return array<int, \Shopsys\FrameworkBundle\Model\Category\CategoryParameter[]> sorted by position, indexed by category id
     */
    public function getCategoryParametersSortedByPositionIndexedByCategoryId(array $categories): array
    {
        if ($categories === []) {
            return [];
        }

        $categoryParameters = $this->getQueryBuilder()
            ->addSelect('p')
            ->join('cp.parameter', 'p')
            ->where('cp.category IN (:categories)')
            ->setParameter('categories', $categories)
            ->orderBy('cp.position')
            ->getQuery()
            ->getResult();

        return $this->indexByCategoryId($categories, $categoryParameters, static fn (CategoryParameter $categoryParameter): CategoryParameter => $categoryParameter);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getParametersCollapsedByCategory(Category $category): array
    {
        return $this->getParametersCollapsedIndexedByCategoryId([$category])[$category->getId()];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     * @return array<int, \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]> indexed by category id
     */
    public function getParametersCollapsedIndexedByCategoryId(array $categories): array
    {
        if ($categories === []) {
            return [];
        }

        $categoryParameters = $this->getQueryBuilder()
            ->addSelect('p')
            ->join('cp.parameter', 'p')
            ->where('cp.category IN (:categories)')
            ->andWhere('cp.collapsed = true')
            ->setParameter('categories', $categories)
            ->getQuery()
            ->getResult();

        return $this->indexByCategoryId($categories, $categoryParameters, static fn (CategoryParameter $categoryParameter): Parameter => $categoryParameter->getParameter());
    }

    /**
     * @template T
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryParameter[] $categoryParameters
     * @param callable(\Shopsys\FrameworkBundle\Model\Category\CategoryParameter): T $mapCategoryParameter
     * @return array<int, T[]>
     */
    protected function indexByCategoryId(
        array $categories,
        array $categoryParameters,
        callable $mapCategoryParameter,
    ): array {
        $indexedByCategoryId = [];

        foreach ($categories as $category) {
            $indexedByCategoryId[$category->getId()] = [];
        }

        foreach ($categoryParameters as $categoryParameter) {
            $indexedByCategoryId[$categoryParameter->getCategory()->getId()][] = $mapCategoryParameter($categoryParameter);
        }

        return $indexedByCategoryId;
    }
}
