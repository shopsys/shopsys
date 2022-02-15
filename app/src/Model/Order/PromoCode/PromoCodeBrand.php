<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Model\Product\Brand\Brand;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="promo_code_brands")
 * @ORM\Entity
 */
class PromoCodeBrand
{
    /**
     * @var \App\Model\Order\PromoCode\PromoCode
     * @ORM\ManyToOne(targetEntity="App\Model\Order\PromoCode\PromoCode")
     * @ORM\JoinColumn(nullable=false, name="promo_code_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\Id
     */
    protected PromoCode $promoCode;

    /**
     * @var \App\Model\Product\Brand\Brand
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Brand\Brand")
     * @ORM\JoinColumn(nullable=false, name="brand_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\Id
     */
    protected Brand $brand;

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \App\Model\Product\Brand\Brand $brand
     */
    public function __construct(PromoCode $promoCode, Brand $brand)
    {
        $this->promoCode = $promoCode;
        $this->brand = $brand;
    }

    /**
     * @return \App\Model\Product\Brand\Brand
     */
    public function getBrand(): Brand
    {
        return $this->brand;
    }
}
