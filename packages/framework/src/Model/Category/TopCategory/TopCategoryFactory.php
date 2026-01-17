<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category\TopCategory;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Category\Category;

class TopCategoryFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(
        Category $category,
        int $domainId,
        int $position,
    ): TopCategory {
        $entityClassName = $this->entityNameResolver->resolve(TopCategory::class);

        return new $entityClassName($category, $domainId, $position);
    }
}
