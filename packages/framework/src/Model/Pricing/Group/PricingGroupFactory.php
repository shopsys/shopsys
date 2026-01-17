<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Group;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class PricingGroupFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(PricingGroupData $data, int $domainId): PricingGroup
    {
        $entityClassName = $this->entityNameResolver->resolve(PricingGroup::class);

        return new $entityClassName($data, $domainId);
    }
}
