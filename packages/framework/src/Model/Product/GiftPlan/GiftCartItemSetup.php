<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Shopsys\FrameworkBundle\Model\Product\Product;

class GiftCartItemSetup
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|null $mainProduct
     */
    public function __construct(
        protected readonly Product $giftProduct,
        protected int $quantity = 0,
        protected $mainProduct = null,
    ) {
    }

    public function getGiftProduct(): Product
    {
        return $this->giftProduct;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    public function getMainProduct()
    {
        return $this->mainProduct;
    }

    /**
     * @return string|null
     */
    public function getMainProductUuid()
    {
        return $this->mainProduct?->getUuid();
    }
}
