<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Autocomplete;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;

class AutocompleteFavoriteBrandFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(Brand $brand, int $domainId, int $position): AutocompleteFavoriteBrand
    {
        $className = $this->entityNameResolver->resolve(AutocompleteFavoriteBrand::class);

        return new $className($brand, $domainId, $position);
    }
}
