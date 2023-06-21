<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;

class PromoCodeProductFactory
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Order\PromoCode\PromoCodeProduct
     */
    public function create(PromoCode $promoCode, Product $product): PromoCodeProduct
    {
        $promoCodeProduct = new PromoCodeProduct($promoCode, $product);
        $this->em->persist($promoCodeProduct);
        $this->em->flush();

        return $promoCodeProduct;
    }
}
