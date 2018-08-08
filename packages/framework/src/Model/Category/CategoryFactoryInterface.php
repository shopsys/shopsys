<?php

namespace Shopsys\FrameworkBundle\Model\Category;

interface CategoryFactoryInterface
{
    public function create(CategoryData $data): Category;
}
