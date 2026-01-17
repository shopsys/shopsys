<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class PriceListFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(PriceListData $data): PriceList
    {
        $entityClassName = $this->entityNameResolver->resolve(PriceList::class);

        return new $entityClassName($data);
    }
}
