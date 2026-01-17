<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Autocomplete;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Category\Category;

class AutocompleteFavoriteCategoryFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(Category $category, int $domainId, int $position): AutocompleteFavoriteCategory
    {
        $className = $this->entityNameResolver->resolve(AutocompleteFavoriteCategory::class);

        return new $className($category, $domainId, $position);
    }
}
