<?php

namespace Shopsys\FrameworkBundle\Model\Category\TopCategory;

use Shopsys\FrameworkBundle\Model\Category\Category;

interface TopCategoryFactoryInterface
{

    public function create(
        Category $category,
        int $domainId,
        int $position
    ): TopCategory;
}
