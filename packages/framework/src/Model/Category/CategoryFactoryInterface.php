<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

interface CategoryFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryData $data
     * @param \Shopsys\FrameworkBundle\Model\Category\Category|null $rootCategory
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function create(CategoryData $data, ?Category $rootCategory): Category;
}
