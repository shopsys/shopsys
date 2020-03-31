<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use App\Model\Product\Type\ProductType;
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
     * @param array $quantifiedProductsByIndex
     * @param array $quantifiedItemsPricesByIndex
     * @param array $quantifiedItemsDiscountsByIndex
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalProductHighPrice
     * @param \App\Model\Transport\Transport|null $transport
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $transportPrice
     * @param \App\Model\Payment\Payment|null $payment
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $paymentPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $roundingPrice
     * @param null $promoCodeDiscountPercent
     * @param \App\Model\Product\Type\ProductType|null $productType
     */
    public function __construct(
        array $quantifiedProductsByIndex,
        array $quantifiedItemsPricesByIndex,
        array $quantifiedItemsDiscountsByIndex,
        Price $productsPrice,
        Price $totalPrice,
        Price $totalProductHighPrice,
        ?Transport $transport = null,
        ?Price $transportPrice = null,
        ?Payment $payment = null,
        ?Price $paymentPrice = null,
        ?Price $roundingPrice = null,
        $promoCodeDiscountPercent = null,
        ?ProductType $productType = null
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
}
