<?php

declare(strict_types=1);

namespace App\Model\Category;

use App\Model\Product\Parameter\ParameterFacade;
use Doctrine\ORM\EntityManagerInterface;

class CategoryParameterFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Category\CategoryParameterRepository $categoryParameterRepository
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     */
    public function __construct(
        private EntityManagerInterface $em,
        private CategoryParameterRepository $categoryParameterRepository,
        private ParameterFacade $parameterFacade,
    ) {
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param int[] $parameterIds
     * @param \App\Model\Product\Parameter\Parameter[] $parametersCollapsed
     */
    public function saveRelation(Category $category, array $parameterIds, array $parametersCollapsed): void
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
        foreach ($parameterIds as $position => $parameterId) {
            $collapsed = false;
            if (array_key_exists($parameterId, $parametersCollapsedById)) {
                $collapsed = true;
            }
            if (array_key_exists($parameterId, $oldCategoryParametersById)) {
                $oldCategoryParameter = $oldCategoryParametersById[$parameterId];
                if ($oldCategoryParameter->isCollapsed() !== $collapsed) {
                    $oldCategoryParameter->setCollapsed($collapsed);
                    $catFlushAfterSaveRelation = true;
                }
                if ($oldCategoryParameter->getPosition() !== $position) {
                    $oldCategoryParameter->setPosition($position);
                    $catFlushAfterSaveRelation = true;
                }
                unset($oldCategoryParametersById[$parameterId]);
                continue;
            }

            $parameter = $this->parameterFacade->getById($parameterId);
            $categoryParameter = new CategoryParameter($category, $parameter, $collapsed, $position);
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
     * @return \App\Model\Product\Parameter\Parameter[]
     */
    public function getParametersCollapsedByCategory(Category $category): array
    {
        return $this->categoryParameterRepository->getParametersCollapsedByCategory($category);
    }
}
