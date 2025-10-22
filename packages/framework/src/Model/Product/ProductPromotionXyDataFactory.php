<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

class ProductPromotionXyDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyData
     */
    protected function createInstance(): ProductPromotionXyData
    {
        return new ProductPromotionXyData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyData
     */
    public function create(): ProductPromotionXyData
    {
        return $this->createInstance();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXy $productPromotionXy
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyData
     */
    public function createFromEntity(ProductPromotionXy $productPromotionXy): ProductPromotionXyData
    {
        $productPromotionXyData = $this->createInstance();

        $productPromotionXyData->buyQuantity = $productPromotionXy->getBuyQuantity();
        $productPromotionXyData->freeQuantity = $productPromotionXy->getFreeQuantity();

        return $productPromotionXyData;
    }
}
