<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class BrandFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(BrandData $data): Brand
    {
        $entityClassName = $this->entityNameResolver->resolve(Brand::class);

        return new $entityClassName($data);
    }
}
