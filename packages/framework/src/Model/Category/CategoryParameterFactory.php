<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;

class CategoryParameterFactory
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
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @param bool $collapsed
     * @param int $position
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryParameter
     */
    public function create(Category $category, Parameter $parameter, bool $collapsed, int $position): CategoryParameter
    {
        $classData = $this->entityNameResolver->resolve(CategoryParameter::class);

        return new $classData($category, $parameter, $collapsed, $position);
    }
}
