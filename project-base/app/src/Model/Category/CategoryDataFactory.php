<?php

declare(strict_types=1);

namespace App\Model\Category;

use Shopsys\FrameworkBundle\Model\Category\CategoryData as BaseCategoryData;
use Shopsys\FrameworkBundle\Model\Category\CategoryDataFactory as BaseCategoryDataFactory;

/**
 * @method \App\Model\Category\CategoryData createFromCategory(\App\Model\Category\Category $category)
 * @method \App\Model\Category\CategoryData create()
 * @method fillNew(\App\Model\Category\CategoryData $categoryData)
 * @method fillFromCategory(\App\Model\Category\CategoryData $categoryData, \App\Model\Category\Category $category)
 */
class CategoryDataFactory extends BaseCategoryDataFactory
{
    /**
     * @return \App\Model\Category\CategoryData
     */
    protected function createInstance(): BaseCategoryData
    {
        $categoryData = new CategoryData();
        $categoryData->image = $this->imageUploadDataFactory->create();

        return $categoryData;
    }
}
