<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Model\Product\Brand\Brand;
use Doctrine\ORM\EntityManagerInterface;

class PromoCodeBrandFactory
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \App\Model\Product\Brand\Brand $brand
     * @return \App\Model\Order\PromoCode\PromoCodeBrand
     */
    public function create(PromoCode $promoCode, Brand $brand): PromoCodeBrand
    {
        $promoCodeBrand = new PromoCodeBrand($promoCode, $brand);
        $this->em->persist($promoCodeBrand);
        $this->em->flush();

        return $promoCodeBrand;
    }
}
