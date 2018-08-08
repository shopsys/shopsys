<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Group;

interface PricingGroupFactoryInterface
{
    public function create(PricingGroupData $data, int $domainId): PricingGroup;
}
