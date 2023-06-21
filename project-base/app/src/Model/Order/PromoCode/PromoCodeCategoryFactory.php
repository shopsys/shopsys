<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Model\Category\Category;
use Doctrine\ORM\EntityManagerInterface;

class PromoCodeCategoryFactory
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \App\Model\Category\Category $category
     * @return \App\Model\Order\PromoCode\PromoCodeCategory
     */
    public function create(PromoCode $promoCode, Category $category): PromoCodeCategory
    {
        $promoCodeCategory = new PromoCodeCategory($promoCode, $category);
        $this->em->persist($promoCodeCategory);
        $this->em->flush();

        return $promoCodeCategory;
    }
}
