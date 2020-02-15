<?php

declare(strict_types=1);

namespace App\Model\Cart\Splitting;

use Shopsys\FrameworkBundle\Model\Pricing\Price;

class SplitCartView
{
    /**
     * @var \App\Model\Cart\Splitting\CartView[]
     */
    private $cartViews;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private $totalPrice;

    /**
     * @param \App\Model\Cart\Splitting\CartView[] $cartViews
     */
    public function __construct(array $cartViews)
    {
        $this->cartViews = $cartViews;
        $this->calculateTotalPrice();
    }

    /**
     * @return \App\Model\Cart\Splitting\CartView[]
     */
    public function getCartViews(): array
    {
        return $this->cartViews;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalPrice(): Price
    {
        return $this->totalPrice;
    }

    private function calculateTotalPrice(): void
    {
        $this->totalPrice = Price::zero();
        foreach ($this->cartViews as $cartView) {
            $this->totalPrice = $this->totalPrice->add($cartView->getOrderPreview()->getTotalPrice());
        }
    }
}
