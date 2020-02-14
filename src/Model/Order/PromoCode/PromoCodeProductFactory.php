<?php

declare(strict_types=1);


namespace App\Model\Order\PromoCode;


use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;

class PromoCodeProductFactory
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function create(PromoCode $promoCode, Product $product)
    {
        $promoCodeProduct = new PromoCodeProduct($promoCode, $product);
        $this->em->persist($promoCodeProduct);
        $this->em->flush();
        return $promoCodeProduct;
    }
}