<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductVideo;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ProductVideoFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(ProductVideoData $productVideoData): ProductVideo
    {
        $entityClassName = $this->entityNameResolver->resolve(ProductVideo::class);

        return new $entityClassName($productVideoData);
    }
}
