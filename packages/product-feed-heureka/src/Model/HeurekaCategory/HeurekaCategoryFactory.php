<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class HeurekaCategoryFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(HeurekaCategoryData $data): HeurekaCategory
    {
        $entityClassName = $this->entityNameResolver->resolve(HeurekaCategory::class);

        return new $entityClassName($data);
    }
}
