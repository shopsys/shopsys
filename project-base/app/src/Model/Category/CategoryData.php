<?php

declare(strict_types=1);

namespace App\Model\Category;

use Shopsys\FrameworkBundle\Model\Category\CategoryData as BaseCategoryData;

/**
 * @property \App\Model\Category\Category|null $parent
 * @property \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[] $parametersCollapsed
 */
class CategoryData extends BaseCategoryData
{
}
