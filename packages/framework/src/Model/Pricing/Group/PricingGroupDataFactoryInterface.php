<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Group;

interface PricingGroupDataFactoryInterface
{
    public function create(): PricingGroupData;

    public function createFromPricingGroup(PricingGroup $pricingGroup): PricingGroupData;
}
