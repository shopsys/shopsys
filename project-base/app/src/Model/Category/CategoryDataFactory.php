<?php

declare(strict_types=1);

namespace App\Model\Category;

use Override;
use Shopsys\FrameworkBundle\Model\Category\Category as BaseCategory;
use Shopsys\FrameworkBundle\Model\Category\CategoryData as BaseCategoryData;
use Shopsys\FrameworkBundle\Model\Category\CategoryDataFactory as BaseCategoryDataFactory;

/**
 * @method fillNew(\App\Model\Category\CategoryData $categoryData)
 * @method int[] getParametersSortedByPositionFilteredByCategory(\App\Model\Category\Category $category)
 * @method \App\Model\Category\CategoryData create()
 */
class CategoryDataFactory extends BaseCategoryDataFactory
{
    /**
     * @param \App\Model\Category\Category $category
     * @return \App\Model\Category\CategoryData
     */
    #[Override]
    public function createFromCategory(BaseCategory $category): BaseCategoryData
    {
        $categoryData = $this->createInstance();
        $this->fillFromCategory($categoryData, $category);

        return $categoryData;
    }

    /**
     * @return \App\Model\Category\CategoryData
     */
    #[Override]
    protected function createInstance(): BaseCategoryData
    {
        $categoryData = new CategoryData();
        $categoryData->image = $this->imageUploadDataFactory->create();

        return $categoryData;
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     * @param \App\Model\Category\Category $category
     */
    #[Override]
    protected function fillFromCategory(BaseCategoryData $categoryData, BaseCategory $category): void
    {
        parent::fillFromCategory($categoryData, $category);

        $parameters = $this->categoryParameterRepository->getParametersCollapsedByCategory($category);
        $categoryData->parametersCollapsed = $parameters;
        $categoryData->parametersPosition = $this->getParametersSortedByPositionFilteredByCategory($category);
    }
}
