<?php

declare(strict_types=1);

namespace App\Model\Category;

use Shopsys\FrameworkBundle\Model\Category\CategoryData as BaseCategoryData;
use Shopsys\FrameworkBundle\Model\Category\CategoryDataFactory as BaseCategoryDataFactory;

/**
 * @method \App\Model\Category\CategoryData createFromCategory(\App\Model\Category\Category $category)
 * @method \App\Model\Category\CategoryData create()
 */
class CategoryDataFactory extends BaseCategoryDataFactory
{
    /**
     * @return \App\Model\Category\CategoryData
     */
    protected function createInstance(): BaseCategoryData
    {
        return new CategoryData();
    }
}
