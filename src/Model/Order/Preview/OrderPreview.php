<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use App\Model\Product\Type\ProductType;
use App\Model\Stock\Stock;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview as BaseOrderPreview;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class OrderPreview extends BaseOrderPreview
{
    /**
     * @var \App\Model\Product\Type\ProductType|null
     */
    private $productType;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private $totalProductHighPrice;

    /**
     * @var \App\Model\Stock\Stock|null
     */
    private $personalPickupStock;

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
     * @var string|null
     */
    private $promoCodeCode;

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
     * @var string|null
     */
    protected $promoCodeIdentifier;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProductsByIndex
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPricesByIndex
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $quantifiedItemsDiscountsByIndex
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalProductHighPrice
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
     * @param \App\Model\Product\Type\ProductType|null $productType
     * @param \App\Model\Stock\Stock|null $personalPickupStock
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $restToFreeTransportPrice
     * @param int|null $percentageOfFreeTransport
     * @param bool|null $transportForFree
     * @param string|null $promoCodeCode;
     * @param string|null $promoCodeIdentifier;
     */
    public function __construct(
        array $quantifiedProductsByIndex,
        array $quantifiedItemsPricesByIndex,
        array $quantifiedItemsDiscountsByIndex,
        Price $productsPrice,
        Price $totalPrice,
        Price $totalProductHighPrice,
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
        ?ProductType $productType = null,
        ?Stock $personalPickupStock = null,
        ?Money $restToFreeTransportPrice = null,
        ?int $percentageOfFreeTransport = null,
        ?bool $transportForFree = false,
        $promoCodeCode = null,
        $promoCodeIdentifier = null
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

        $this->productType = $productType;
        $this->totalProductHighPrice = $totalProductHighPrice;
        $this->personalPickupStock = $personalPickupStock;
        $this->productsAvailability = $productsAvailability;
        $this->restToFreeTransportPrice = $restToFreeTransportPrice;
        $this->percentageOfFreeTransport = $percentageOfFreeTransport;
        $this->transportForFree = $transportForFree;
        $this->promoCodeCode = $promoCodeCode;
        $this->quantifiedItemsDiscountPricesByIndex = $quantifiedItemsDiscountPricesByIndex;
        $this->productsFullPrice = $productsFullPrice;
        $this->totalPriceDiscount = $totalPriceDiscount;
        $this->promoCodeIdentifier = $promoCodeIdentifier;
    }

    /**
     * @return \App\Model\Product\Type\ProductType
     */
    public function getProductType(): ProductType
    {
        if ($this->productType === null) {
            throw new \RuntimeException('Product type is null. Please create OrderPreview with this parameter for your scenario.');
        }

        return $this->productType;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalProductHighPrice(): Price
    {
        return $this->totalProductHighPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getSubHighAndLowPrice(): Price
    {
        return $this->totalProductHighPrice->subtract($this->totalPrice);
    }

    /**
     * @return \App\Model\Stock\Stock|null
     */
    public function getPersonalPickupStock(): ?Stock
    {
        return $this->personalPickupStock;
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
        return $this->promoCodeCode;
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
        return $this->promoCodeIdentifier;
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function hasAnyProductCommonPrice(int $domainId): bool
    {
        foreach ($this->quantifiedProductsByIndex as $quantifiedproduct) {
            /**
             * @var \App\Model\Product\Product
             */
            $product = $quantifiedproduct->getProduct();
            $highPrice = $product->getHighPriceWithVat($domainId);

            if (!is_null($highPrice)
                && $highPrice->isGreaterThan(Money::zero())
                && $highPrice->isGreaterThan($product->getSellingPriceWithVat($domainId))
            ) {
                return true;
            }
        }

        return false;
    }
}
