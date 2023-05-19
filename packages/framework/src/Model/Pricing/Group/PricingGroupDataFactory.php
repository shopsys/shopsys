<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Group;

class PricingGroupDataFactory implements PricingGroupDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData
     */
    protected function createInstance(): PricingGroupData
    {
        return new PricingGroupData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData
     */
    public function create(): PricingGroupData
    {
        return $this->createInstance();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData
     */
    public function createFromPricingGroup(PricingGroup $pricingGroup): PricingGroupData
    {
        $pricingGroupData = $this->createInstance();
        $this->fillFromPricingGroup($pricingGroupData, $pricingGroup);

        return $pricingGroupData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData $pricingGroupData
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     */
    protected function fillFromPricingGroup(PricingGroupData $pricingGroupData, PricingGroup $pricingGroup)
    {
        $pricingGroupData->name = $pricingGroup->getName();
    }
}
