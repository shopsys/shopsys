<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class GiftPlanFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(GiftPlanData $giftPlanData): GiftPlan
    {
        $entityClassName = $this->entityNameResolver->resolve(GiftPlan::class);

        return new $entityClassName($giftPlanData);
    }
}
