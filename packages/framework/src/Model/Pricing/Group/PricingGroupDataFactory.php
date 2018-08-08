<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Group;

class PricingGroupDataFactory implements PricingGroupDataFactoryInterface
{
    public function create(): PricingGroupData
    {
        return new PricingGroupData();
    }

    public function createFromPricingGroup(PricingGroup $pricingGroup): PricingGroupData
    {
        $pricingGroupData = new PricingGroupData();
        $this->fillFromPricingGroup($pricingGroupData, $pricingGroup);

        return $pricingGroupData;
    }

    protected function fillFromPricingGroup(PricingGroupData $pricingGroupData, PricingGroup $pricingGroup): void
    {
        $pricingGroupData->name = $pricingGroup->getName();
        $pricingGroupData->coefficient = $pricingGroup->getCoefficient();
    }
}
