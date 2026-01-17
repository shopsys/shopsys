<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

class GiftPlanDataFactory
{
    protected function createInstance(): GiftPlanData
    {
        return new GiftPlanData();
    }

    public function create(): GiftPlanData
    {
        return $this->createInstance();
    }

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
