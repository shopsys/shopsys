<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

/**
 * @ORM\Table(name="promo_code_pricing_groups")
 * @ORM\Entity
 */
class PromoCodePricingGroup
{
    /**
     * @var \App\Model\Order\PromoCode\PromoCode
     * @ORM\ManyToOne(targetEntity="App\Model\Order\PromoCode\PromoCode")
     * @ORM\JoinColumn(nullable=false, name="promo_code_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\Id
     */
    private PromoCode $promoCode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup")
     * @ORM\JoinColumn(nullable=false, name="pricing_group_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\Id
     */
    private PricingGroup $pricingGroup;

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     */
    public function __construct(PromoCode $promoCode, PricingGroup $pricingGroup)
    {
        $this->promoCode = $promoCode;
        $this->pricingGroup = $pricingGroup;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function getPricingGroup(): PricingGroup
    {
        return $this->pricingGroup;
    }
}
