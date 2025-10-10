<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Autocomplete;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Category\Category;

class AutocompleteFavoriteCategoryFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param int $domainId
     * @param int $position
     * @return \Shopsys\FrameworkBundle\Model\Autocomplete\AutocompleteFavoriteCategory
     */
    public function create(Category $category, int $domainId, int $position): AutocompleteFavoriteCategory
    {
        $className = $this->entityNameResolver->resolve(AutocompleteFavoriteCategory::class);

        return new $className($category, $domainId, $position);
    }
}
