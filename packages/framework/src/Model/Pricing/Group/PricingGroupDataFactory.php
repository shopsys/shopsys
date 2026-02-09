<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Group;

class PricingGroupDataFactory
{
    protected function createInstance(): PricingGroupData
    {
        return new PricingGroupData();
    }

    public function create(): PricingGroupData
    {
        return $this->createInstance();
    }

    public function createFromPricingGroup(PricingGroup $pricingGroup): PricingGroupData
    {
        $pricingGroupData = $this->createInstance();
        $this->fillFromPricingGroup($pricingGroupData, $pricingGroup);

        return $pricingGroupData;
    }

    protected function fillFromPricingGroup(PricingGroupData $pricingGroupData, PricingGroup $pricingGroup): void
    {
        $pricingGroupData->name = $pricingGroup->getName();
    }
}
