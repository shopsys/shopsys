<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use Shopsys\FrameworkBundle\Model\Pricing\Price;

class SplitOrderPreview
{
    /**
     * @var \App\Model\Order\Preview\OrderPreview[]
     */
    private $orderPreviews;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private $totalPrice;

    /**
     * @param \App\Model\Order\Preview\OrderPreview[] $orderPreviews
     */
    public function __construct(array $orderPreviews)
    {
        $this->orderPreviews = $orderPreviews;
        $this->calculateTotalPrice();
    }

    /**
     * @return \App\Model\Order\Preview\OrderPreview[]
     */
    public function getOrderPreviews(): array
    {
        return $this->orderPreviews;
    }

    /**
     * @return bool
     */
    public function hasMorePreviews(): bool
    {
        return count($this->orderPreviews) > 1;
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
        foreach ($this->orderPreviews as $orderPreview) {
            $this->totalPrice = $this->totalPrice->add($orderPreview->getTotalPrice());
        }
    }
}
