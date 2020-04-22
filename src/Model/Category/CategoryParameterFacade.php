<?php

declare(strict_types=1);

namespace App\Model\Category;

use Doctrine\ORM\EntityManagerInterface;

class CategoryParameterFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \App\Model\Category\CategoryParameterRepository
     */
    private $categoryParameterRepository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Category\CategoryParameterRepository $categoryParameterRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        CategoryParameterRepository $categoryParameterRepository
    ) {
        $this->em = $em;
        $this->categoryParameterRepository = $categoryParameterRepository;
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param \App\Model\Product\Parameter\Parameter[] $parameters
     * @param \App\Model\Product\Parameter\Parameter[] $parametersCollapsed
     */
    public function saveRelation(Category $category, array $parameters, array $parametersCollapsed): void
    {
        $parametersCollapsedById = [];
        foreach ($parametersCollapsed as $parameterCollapsed) {
            $parametersCollapsedById[$parameterCollapsed->getId()] = true;
        }

        $oldCategoryParameters = $this->categoryParameterRepository->getAllByCategory($category);
        $oldCategoryParametersById = [];
        foreach ($oldCategoryParameters as $oldCategoryParameter) {
            $oldCategoryParametersById[$oldCategoryParameter->getParameter()->getId()] = $oldCategoryParameter;
        }
        $catFlushAfterSaveRelation = false;
        foreach ($parameters as $parameter) {
            $collapsed = false;
            if (array_key_exists($parameter->getId(), $parametersCollapsedById)) {
                $collapsed = true;
            }
            if (array_key_exists($parameter->getId(), $oldCategoryParametersById)) {
                $oldCategoryParameter = $oldCategoryParametersById[$parameter->getId()];
                if ($oldCategoryParameter->isCollapsed() !== $collapsed) {
                    $oldCategoryParameter->setCollapsed($collapsed);
                    $catFlushAfterSaveRelation = true;
                }
                unset($oldCategoryParametersById[$parameter->getId()]);
                continue;
            }

            $categoryParameter = new CategoryParameter($category, $parameter, $collapsed);

            $this->em->persist($categoryParameter);
            $catFlushAfterSaveRelation = true;
        }

        foreach ($oldCategoryParametersById as $oldCategoryParameter) {
            $this->em->remove($oldCategoryParameter);
            $catFlushAfterSaveRelation = true;
        }

        if ($catFlushAfterSaveRelation) {
            $this->em->flush();
        }
    }

    /**
     * @param \App\Model\Category\Category $category
     * @return array
     */
    public function getParametersCollapsedIndexedByIdForCategory(Category $category): array
    {
        $parametersCollapsedByCategory = $this->categoryParameterRepository->getParametersCollapsedByCategory($category);

        $parametersCollapsed = [];
        foreach ($parametersCollapsedByCategory as $item) {
            $parametersCollapsed[$item->getId()] = $item->getId();
        }

        return $parametersCollapsed;
    }
}
