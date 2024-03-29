<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class PromoCodePricingGroupFactory
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \App\Model\Order\PromoCode\PromoCodePricingGroup
     */
    public function create(PromoCode $promoCode, PricingGroup $pricingGroup): PromoCodePricingGroup
    {
        $promoCodePricingGroup = new PromoCodePricingGroup($promoCode, $pricingGroup);
        $this->em->persist($promoCodePricingGroup);
        $this->em->flush();

        return $promoCodePricingGroup;
    }
}
