<?php

namespace Shopsys\FrameworkBundle\Model\Category\TopCategory;

use Shopsys\FrameworkBundle\Model\Category\Category;

class TopCategoryFactory implements TopCategoryFactoryInterface
{
    public function create(
        Category $category,
        int $domainId,
        int $position
    ): TopCategory {
        return new TopCategory($category, $domainId, $position);
    }
}
