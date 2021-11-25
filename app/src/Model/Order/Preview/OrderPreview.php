<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use App\Model\Order\PromoCode\PromoCode;
use App\Model\Store\Store;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview as BaseOrderPreview;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class OrderPreview extends BaseOrderPreview
{
    /**
     * @var \App\Model\Store\Store|null
     */
    private $personalPickupStore;

    /**
     * @var string[]
     */
    private $productsAvailability;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    private $restToFreeTransportPrice;

    /**
     * @var int|null
     */
    private $percentageOfFreeTransport;

    /**
     * @var bool
     */
    private $transportForFree;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    protected $quantifiedItemsDiscountPricesByIndex;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private $productsFullPrice;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private $totalPriceDiscount;

    /**
     * @var \App\Model\Order\PromoCode\PromoCode|null
     */
    private ?PromoCode $promoCode;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProductsByIndex
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPricesByIndex
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $quantifiedItemsDiscountsByIndex
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalPrice
     * @param string[] $productsAvailability
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[]|null[] $quantifiedItemsDiscountPricesByIndex
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsFullPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalPriceDiscount
     * @param \App\Model\Transport\Transport|null $transport
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $transportPrice
     * @param \App\Model\Payment\Payment|null $payment
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $paymentPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $roundingPrice
     * @param null $promoCodeDiscountPercent
     * @param \App\Model\Store\Store|null $personalPickupStore
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $restToFreeTransportPrice
     * @param int|null $percentageOfFreeTransport
     * @param bool|null $transportForFree
     * @param \App\Model\Order\PromoCode\PromoCode|null $promoCode
     */
    public function __construct(
        array $quantifiedProductsByIndex,
        array $quantifiedItemsPricesByIndex,
        array $quantifiedItemsDiscountsByIndex,
        Price $productsPrice,
        Price $totalPrice,
        array $productsAvailability,
        array $quantifiedItemsDiscountPricesByIndex,
        Price $productsFullPrice,
        Price $totalPriceDiscount,
        ?Transport $transport = null,
        ?Price $transportPrice = null,
        ?Payment $payment = null,
        ?Price $paymentPrice = null,
        ?Price $roundingPrice = null,
        $promoCodeDiscountPercent = null,
        ?Store $personalPickupStore = null,
        ?Money $restToFreeTransportPrice = null,
        ?int $percentageOfFreeTransport = null,
        ?bool $transportForFree = false,
        ?PromoCode $promoCode = null
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
            $promoCodeDiscountPercent
        );

        $this->personalPickupStore = $personalPickupStore;
        $this->productsAvailability = $productsAvailability;
        $this->restToFreeTransportPrice = $restToFreeTransportPrice;
        $this->percentageOfFreeTransport = $percentageOfFreeTransport;
        $this->transportForFree = $transportForFree;
        $this->quantifiedItemsDiscountPricesByIndex = $quantifiedItemsDiscountPricesByIndex;
        $this->productsFullPrice = $productsFullPrice;
        $this->totalPriceDiscount = $totalPriceDiscount;
        $this->promoCode = $promoCode;
    }

    /**
     * @return \App\Model\Store\Store|null
     */
    public function getPersonalPickupStore(): ?Store
    {
        return $this->personalPickupStore;
    }

    /**
     * @return string[]
     */
    public function getProductsAvailability()
    {
        return $this->productsAvailability;
    }

    /**
     * @return int|null
     */
    public function getPercentageOfFreeTransport(): ?int
    {
        return $this->percentageOfFreeTransport;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getRestToFreeTransportPrice(): ?Money
    {
        return $this->restToFreeTransportPrice;
    }

    /**
     * @return bool
     */
    public function isTransportForFree(): bool
    {
        return $this->transportForFree;
    }

    /**
     * @return string|null
     */
    public function getPromoCodeCode(): ?string
    {
        return $this->promoCode !== null ? $this->promoCode->getCode() : null;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    public function getQuantifiedItemsDiscountPrices()
    {
        return $this->quantifiedItemsDiscountPricesByIndex;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getProductsFullPrice(): Price
    {
        return $this->productsFullPrice;
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
}
