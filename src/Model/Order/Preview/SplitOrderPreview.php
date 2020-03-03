<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use App\Model\Payment\Payment;
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
     * @var \App\Model\Payment\Payment|null
     */
    private $payment;

    /**
     * @param \App\Model\Order\Preview\OrderPreview[] $orderPreviews
     * @param \App\Model\Payment\Payment|null $payment
     */
    public function __construct(array $orderPreviews, ?Payment $payment)
    {
        $this->orderPreviews = $orderPreviews;
        $this->payment = $payment;
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

    /**
     * @return \App\Model\Payment\Payment
     */
    public function getPayment(): Payment
    {
        if ($this->payment === null) {
            throw new \RuntimeException('Payment is not set. Please set it for this scenario');
        }
        return $this->payment;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getProductsPrice(): Price
    {
        $productsPrice = Price::zero();
        foreach ($this->orderPreviews as $orderPreview) {
            $productsPrice = $productsPrice->add($orderPreview->getProductsPrice());
        }

        return $productsPrice;
    }

    public function getRoundingPrice()
    {
    }
}
