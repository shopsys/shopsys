<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Group;

class PricingGroupFactory implements PricingGroupFactoryInterface
{
    public function create(PricingGroupData $data, int $domainId): PricingGroup
    {
        return new PricingGroup($data, $domainId);
    }
}
