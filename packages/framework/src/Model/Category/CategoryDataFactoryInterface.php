<?php

namespace Shopsys\FrameworkBundle\Model\Category;

interface CategoryDataFactoryInterface
{
    public function createFromCategory(Category $category): CategoryData;

    public function create(): CategoryData;
}
