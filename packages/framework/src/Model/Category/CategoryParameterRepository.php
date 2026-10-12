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

        return $this->indexByCategoryId($categories, $categoryParameters);
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

        $categoryParametersIndexedByCategoryId = $this->indexByCategoryId($categories, $categoryParameters);
        $parametersIndexedByCategoryId = [];

        foreach ($categoryParametersIndexedByCategoryId as $categoryId => $categoryParametersOfCategory) {
            $parametersIndexedByCategoryId[$categoryId] = array_map(
                static fn (CategoryParameter $categoryParameter): Parameter => $categoryParameter->getParameter(),
                $categoryParametersOfCategory,
            );
        }

        return $parametersIndexedByCategoryId;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryParameter[] $categoryParameters
     * @return array<int, \Shopsys\FrameworkBundle\Model\Category\CategoryParameter[]>
     */
    protected function indexByCategoryId(array $categories, array $categoryParameters): array
    {
        $categoryParametersIndexedByCategoryId = [];

        foreach ($categories as $category) {
            $categoryParametersIndexedByCategoryId[$category->getId()] = [];
        }

        foreach ($categoryParameters as $categoryParameter) {
            $categoryParametersIndexedByCategoryId[$categoryParameter->getCategory()->getId()][] = $categoryParameter;
        }

        return $categoryParametersIndexedByCategoryId;
    }
}
