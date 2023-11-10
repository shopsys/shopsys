<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use App\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview as BaseOrderPreview;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class OrderPreview extends BaseOrderPreview
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProductsByIndex
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPricesByIndex
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $quantifiedItemsDiscountsByIndex
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[]|null[] $quantifiedItemsDiscountPricesByIndex
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalPriceDiscount
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalPriceWithoutDiscountTransportAndPayment
     * @param \App\Model\Transport\Transport|null $transport
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $transportPrice
     * @param \App\Model\Payment\Payment|null $payment
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $paymentPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $roundingPrice
     * @param null $promoCodeDiscountPercent
     * @param \Shopsys\FrameworkBundle\Model\Store\Store|null $personalPickupStore
     * @param \App\Model\Order\PromoCode\PromoCode|null $promoCode
     */
    public function __construct(
        array $quantifiedProductsByIndex,
        array $quantifiedItemsPricesByIndex,
        array $quantifiedItemsDiscountsByIndex,
        Price $productsPrice,
        Price $totalPrice,
        protected array $quantifiedItemsDiscountPricesByIndex,
        private Price $totalPriceDiscount,
        private Price $totalPriceWithoutDiscountTransportAndPayment,
        ?Transport $transport = null,
        ?Price $transportPrice = null,
        ?Payment $payment = null,
        ?Price $paymentPrice = null,
        ?Price $roundingPrice = null,
        $promoCodeDiscountPercent = null,
        private ?Store $personalPickupStore = null,
        private ?PromoCode $promoCode = null,
    ) {
        parent::__construct(
            $quantifiedProductsByIndex,
            $quantifiedItemsPricesByIndex,
            $quantifiedItemsDiscountsByIndex,
            $productsPrice,
            $totalPrice,
            $transport,
            $transportPrice,
            $payment,
            $paymentPrice,
            $roundingPrice,
            $promoCodeDiscountPercent,
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Store\Store|null
     */
    public function getPersonalPickupStore(): ?Store
    {
        return $this->personalPickupStore;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalPriceDiscount(): Price
    {
        return $this->totalPriceDiscount;
    }

    /**
     * @return string|null
     */
    public function getPromoCodeIdentifier(): ?string
    {
        return $this->promoCode !== null ? $this->promoCode->getIdentifier() : null;
    }

    /**
     * @return \App\Model\Order\PromoCode\PromoCode|null
     */
    public function getPromoCode(): ?PromoCode
    {
        return $this->promoCode;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalPriceWithoutDiscountTransportAndPayment(): Price
    {
        return $this->totalPriceWithoutDiscountTransportAndPayment;
    }
}
