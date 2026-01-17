<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;

class PromoCodeBrandFactory
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(PromoCode $promoCode, Brand $brand): PromoCodeBrand
    {
        $className = $this->entityNameResolver->resolve(PromoCodeBrand::class);
        $promoCodeBrand = new $className($promoCode, $brand);
        $this->em->persist($promoCodeBrand);
        $this->em->flush();

        return $promoCodeBrand;
    }
}
