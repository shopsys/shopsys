<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;

/**
 * @ORM\Table(name="promo_code_brands")
 * @ORM\Entity
 */
class PromoCodeBrand
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode")
     * @ORM\JoinColumn(nullable=false, name="promo_code_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\Id
     */
    protected $promoCode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Brand\Brand")
     * @ORM\JoinColumn(nullable=false, name="brand_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\Id
     */
    protected $brand;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     */
    public function __construct(PromoCode $promoCode, Brand $brand)
    {
        $this->promoCode = $promoCode;
        $this->brand = $brand;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }
}
