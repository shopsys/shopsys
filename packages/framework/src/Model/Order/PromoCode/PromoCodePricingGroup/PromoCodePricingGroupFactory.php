<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class PromoCodePricingGroupFactory
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroup
     */
    public function create(PromoCode $promoCode, PricingGroup $pricingGroup): PromoCodePricingGroup
    {
        $entityName = $this->entityNameResolver->resolve(PromoCodePricingGroup::class);
        $promoCodePricingGroup = new $entityName($promoCode, $pricingGroup);
        $this->em->persist($promoCodePricingGroup);
        $this->em->flush();

        return $promoCodePricingGroup;
    }
}
