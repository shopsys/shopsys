<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ZboziCategoryFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(ZboziCategoryData $data): ZboziCategory
    {
        $entityClassName = $this->entityNameResolver->resolve(ZboziCategory::class);

        return new $entityClassName($data);
    }
}
