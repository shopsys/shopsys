<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use App\Model\Payment\Payment;
use App\Model\Product\Type\ProductType;
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
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    private $roundingPrice;

    /**
     * @var \App\Model\Payment\Payment|null
     */
    private $payment;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    private $paymentPrice;

    /**
     * @param \App\Model\Order\Preview\OrderPreview[] $orderPreviews
     * @param \App\Model\Payment\Payment|null $payment
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $roundingPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $paymentPrice
     */
    public function __construct(
        array $orderPreviews,
        ?Payment $payment,
        Price $totalPrice,
        ?Price $roundingPrice,
        ?Price $paymentPrice
    ) {
        $this->orderPreviews = $orderPreviews;
        $this->payment = $payment;
        $this->totalPrice = $totalPrice;
        $this->roundingPrice = $roundingPrice;
        $this->paymentPrice = $paymentPrice;
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
    public function getPaymentPrice(): Price
    {
        if ($this->paymentPrice === null) {
            throw new \RuntimeException('Payment price is not set. Please set it for this scenario');
        }
        return $this->paymentPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    public function getRoundingPrice(): ?Price
    {
        return $this->roundingPrice;
    }

    /**
     * @return \App\Model\Product\Type\ProductType
     */
    public function getProductTypeForCommonItems(): ProductType
    {
        $firstOrderPreview = reset($this->orderPreviews);
        if ($firstOrderPreview === false) {
            throw new \RuntimeException('In this scenario has to be set least one OrderPreview to getting common ProductType.');
        }

        return $firstOrderPreview->getProductType();
    }
}
