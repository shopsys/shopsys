<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class GiftPlanFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanData $giftPlanData
     * @return \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan
     */
    public function create(GiftPlanData $giftPlanData): GiftPlan
    {
        $entityClassName = $this->entityNameResolver->resolve(GiftPlan::class);

        return new $entityClassName($giftPlanData);
    }
}
