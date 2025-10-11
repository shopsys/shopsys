<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Shopsys\FrameworkBundle\Model\Product\Product;

class GiftCartItemSetup
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $giftProduct
     * @param int $quantity
     */
    public function __construct(protected readonly Product $giftProduct, protected int $quantity = 0)
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getGiftProduct(): Product
    {
        return $this->giftProduct;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function addQuantity(int $quantity): void
    {
        $this->quantity += $quantity;
    }
}
