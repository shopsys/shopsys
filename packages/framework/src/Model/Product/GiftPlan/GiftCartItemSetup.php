<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Shopsys\FrameworkBundle\Model\Product\Product;

class GiftCartItemSetup
{
    public function __construct(protected readonly Product $giftProduct, protected int $quantity = 0)
    {
    }

    public function getGiftProduct(): Product
    {
        return $this->giftProduct;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function addQuantity(int $quantity): void
    {
        $this->quantity += $quantity;
    }
}
