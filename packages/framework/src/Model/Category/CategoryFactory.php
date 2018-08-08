<?php

namespace Shopsys\FrameworkBundle\Model\Category;

class CategoryFactory implements CategoryFactoryInterface
{
    public function create(CategoryData $data): Category
    {
        return new Category($data);
    }
}
