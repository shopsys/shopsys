<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Product\Product;

class PromoCodeProductFactory
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(PromoCode $promoCode, Product $product): PromoCodeProduct
    {
        $entityName = $this->entityNameResolver->resolve(PromoCodeProduct::class);
        $promoCodeProduct = new $entityName($promoCode, $product);
        $this->em->persist($promoCodeProduct);
        $this->em->flush();

        return $promoCodeProduct;
    }
}
