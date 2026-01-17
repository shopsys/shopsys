<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;

class PromoCodeCategoryFactory
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(PromoCode $promoCode, Category $category): PromoCodeCategory
    {
        $className = $this->entityNameResolver->resolve(PromoCodeCategory::class);
        $promoCodeCategory = new $className($promoCode, $category);
        $this->em->persist($promoCodeCategory);
        $this->em->flush();

        return $promoCodeCategory;
    }
}
