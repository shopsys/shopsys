<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Autocomplete;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;

class AutocompleteFavoriteBrandFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @param int $domainId
     * @param int $position
     * @return \Shopsys\FrameworkBundle\Model\Autocomplete\AutocompleteFavoriteBrand
     */
    public function create(Brand $brand, int $domainId, int $position): AutocompleteFavoriteBrand
    {
        $className = $this->entityNameResolver->resolve(AutocompleteFavoriteBrand::class);

        return new $className($brand, $domainId, $position);
    }
}
