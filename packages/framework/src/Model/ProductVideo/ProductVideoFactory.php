<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductVideo;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ProductVideoFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoData $productVideoData
     * @return \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideo
     */
    public function create(ProductVideoData $productVideoData): ProductVideo
    {
        $entityClassName = $this->entityNameResolver->resolve(ProductVideo::class);

        return new $entityClassName($productVideoData);
    }
}
