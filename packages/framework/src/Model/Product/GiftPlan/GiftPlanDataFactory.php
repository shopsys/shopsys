<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

class GiftPlanDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanData
     */
    protected function createInstance(): GiftPlanData
    {
        return new GiftPlanData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanData
     */
    public function create(): GiftPlanData
    {
        return $this->createInstance();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan $giftPlan
     * @return \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanData
     */
    public function createFromGiftPlan(GiftPlan $giftPlan): GiftPlanData
    {
        $giftPlanData = $this->createInstance();
        $giftPlanData->uuid = $giftPlan->getUuid();
        $giftPlanData->domainId = $giftPlan->getDomainId();
        $giftPlanData->name = $giftPlan->getName();
        $giftPlanData->validFrom = $giftPlan->getValidFrom();
        $giftPlanData->validTo = $giftPlan->getValidTo();
        $giftPlanData->giftProduct = $giftPlan->getGiftProduct();
        $giftPlanData->mainProducts = $giftPlan->getMainProducts();

        return $giftPlanData;
    }
}
