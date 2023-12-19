<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;

class CategoryParameterFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryParameterRepository $categoryParameterRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CategoryParameterRepository $categoryParameterRepository,
        protected readonly ParameterFacade $parameterFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param int[] $parameterIds
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[] $parametersCollapsed
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
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getParametersCollapsedByCategory(Category $category): array
    {
        return $this->categoryParameterRepository->getParametersCollapsedByCategory($category);
    }
}
