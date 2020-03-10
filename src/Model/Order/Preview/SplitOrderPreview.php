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
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private $productsPrice;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    private $roundingPrice;

    /**
     * @var \App\Model\Payment\Payment|null
     */
    private $payment;

    /**
     * @var \App\Model\Order\Preview\TransportAndPaymentPricesPreview|null
     */
    private $transportAndPaymentPricesPreview;

    /**
     * @param \App\Model\Order\Preview\OrderPreview[] $orderPreviews
     * @param \App\Model\Payment\Payment|null $payment
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $roundingPrice
     */
    public function __construct(
        array $orderPreviews,
        ?Payment $payment,
        Price $totalPrice,
        Price $productsPrice,
        ?Price $roundingPrice
    ) {
        $this->orderPreviews = $orderPreviews;
        $this->payment = $payment;
        $this->totalPrice = $totalPrice;
        $this->productsPrice = $productsPrice;
        $this->roundingPrice = $roundingPrice;
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
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getProductsPrice(): Price
    {
        return $this->productsPrice;
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

    /**
     * @return \App\Model\Order\Preview\TransportAndPaymentPricesPreview
     */
    public function getTransportAndPaymentPricesPreview(): TransportAndPaymentPricesPreview
    {
        if ($this->transportAndPaymentPricesPreview === null) {
            throw new \RuntimeException('TransportAndPaymentPricesPreview is not set. Please set it for this scenario');
        }

        return $this->transportAndPaymentPricesPreview;
    }

    /**
     * @param \App\Model\Order\Preview\TransportAndPaymentPricesPreview $transportAndPaymentPricesPreview
     */
    public function setTransportAndPaymentPricesPreview(TransportAndPaymentPricesPreview $transportAndPaymentPricesPreview): void
    {
        if ($this->transportAndPaymentPricesPreview !== null) {
            throw new \RuntimeException('TransportAndPaymentPricesPreview is already set. It cannot be changed');
        }

        $this->transportAndPaymentPricesPreview = $transportAndPaymentPricesPreview;
    }
}
