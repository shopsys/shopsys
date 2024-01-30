<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class HeurekaCategoryFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryData $data
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategory
     */
    public function create(HeurekaCategoryData $data): HeurekaCategory
    {
        $entityClassName = $this->entityNameResolver->resolve(HeurekaCategory::class);

        return new $entityClassName($data);
    }
}
