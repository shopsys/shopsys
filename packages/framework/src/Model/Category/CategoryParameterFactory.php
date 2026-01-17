<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;

class CategoryParameterFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(Category $category, Parameter $parameter, bool $collapsed, int $position): CategoryParameter
    {
        $entityClassName = $this->entityNameResolver->resolve(CategoryParameter::class);

        return new $entityClassName($category, $parameter, $collapsed, $position);
    }
}
