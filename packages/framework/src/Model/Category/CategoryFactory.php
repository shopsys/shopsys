<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CategoryFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(CategoryData $data, ?Category $rootCategory): Category
    {
        $entityClassName = $this->entityNameResolver->resolve(Category::class);
        $category = new $entityClassName($data);

        if ($rootCategory !== null && $category->getParent() === null) {
            $category->setParent($rootCategory);
        }

        return $category;
    }
}
