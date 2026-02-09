<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class PromoCodePricingGroupFactory
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(PromoCode $promoCode, PricingGroup $pricingGroup): PromoCodePricingGroup
    {
        $entityName = $this->entityNameResolver->resolve(PromoCodePricingGroup::class);
        $promoCodePricingGroup = new $entityName($promoCode, $pricingGroup);
        $this->em->persist($promoCodePricingGroup);
        $this->em->flush();

        return $promoCodePricingGroup;
    }
}
