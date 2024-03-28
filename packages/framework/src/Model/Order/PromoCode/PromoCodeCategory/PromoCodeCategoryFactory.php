<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;

class PromoCodeCategoryFactory
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategory
     */
    public function create(PromoCode $promoCode, Category $category): PromoCodeCategory
    {
        $className = $this->entityNameResolver->resolve(PromoCodeCategory::class);
        $promoCodeCategory = new $className($promoCode, $category);
        $this->em->persist($promoCodeCategory);
        $this->em->flush();

        return $promoCodeCategory;
    }
}
