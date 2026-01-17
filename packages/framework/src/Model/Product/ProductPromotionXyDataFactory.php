<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

class ProductPromotionXyDataFactory
{
    protected function createInstance(): ProductPromotionXyData
    {
        return new ProductPromotionXyData();
    }

    public function create(): ProductPromotionXyData
    {
        return $this->createInstance();
    }

    public function createFromEntity(ProductPromotionXy $productPromotionXy): ProductPromotionXyData
    {
        $productPromotionXyData = $this->createInstance();

        $productPromotionXyData->buyQuantity = $productPromotionXy->getBuyQuantity();
        $productPromotionXyData->freeQuantity = $productPromotionXy->getFreeQuantity();

        return $productPromotionXyData;
    }
}
