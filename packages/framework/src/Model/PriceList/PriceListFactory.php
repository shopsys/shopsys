<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class PriceListFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListData $data
     * @return \Shopsys\FrameworkBundle\Model\PriceList\PriceList
     */
    public function create(PriceListData $data): PriceList
    {
        $entityClassName = $this->entityNameResolver->resolve(PriceList::class);

        return new $entityClassName($data);
    }
}
