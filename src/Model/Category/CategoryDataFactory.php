<?php

declare(strict_types=1);

namespace App\Model\Category;

use Shopsys\FrameworkBundle\Model\Category\Category as BaseCategory;
use Shopsys\FrameworkBundle\Model\Category\CategoryData as BaseCategoryData;
use Shopsys\FrameworkBundle\Model\Category\CategoryDataFactory as BaseCategoryDataFactory;

class CategoryDataFactory extends BaseCategoryDataFactory
{
    /**
     * @param \App\Model\Category\Category $category
     * @return \App\Model\Category\CategoryData
     */
    public function createFromCategory(BaseCategory $category): BaseCategoryData
    {
        $categoryData = new CategoryData();
        $this->fillFromCategory($categoryData, $category);

        return $categoryData;
    }

    /**
     * @return \App\Model\Category\CategoryData
     */
    public function create(): BaseCategoryData
    {
        $categoryData = new CategoryData();
        $this->fillNew($categoryData);

        return $categoryData;
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     */
    protected function fillNew(BaseCategoryData $categoryData)
    {
        parent::fillNew($categoryData);

        foreach ($this->domain->getAllIds() as $domainId) {
            $categoryData->shortDescription[$domainId] = null;
        }
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     * @param \App\Model\Category\Category $category
     */
    protected function fillFromCategory(BaseCategoryData $categoryData, BaseCategory $category)
    {
        parent::fillFromCategory($categoryData, $category);

        foreach ($this->domain->getAllIds() as $domainId) {
            $categoryData->shortDescription[$domainId] = $category->getShortDescription($domainId);
        }

        $categoryData->akeneoCode = $category->getAkeneoCode();
        $categoryData->svgIcon = $category->getSvgIcon();
    }
}
