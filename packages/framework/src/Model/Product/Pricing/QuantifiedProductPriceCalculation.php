<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPriceInterface;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidInputPriceTypeException;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;

class QuantifiedProductPriceCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation $priceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanSettingFacade $giftPlanSettingFacade
     */
    public function __construct(
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        protected readonly PriceCalculation $priceCalculation,
        protected readonly PricingSetting $pricingSetting,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly GiftPlanSettingFacade $giftPlanSettingFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPriceInterface
     */
    protected function calculatePrice(
        QuantifiedProduct $quantifiedProduct,
        int $domainId,
        ?CustomerUser $customerUser = null,
    ): QuantifiedItemPriceInterface {
        $quantifiedPricesResult = $this->calculateQuantifiedBasicAndSellingPrice(
            $quantifiedProduct,
            $domainId,
            $customerUser,
        );

        return $quantifiedPricesResult->sellingQuantifiedItemPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPriceInterface
     */
    public function calculateGiftPrice(
        QuantifiedProduct $quantifiedProduct,
        int $domainId,
    ): QuantifiedItemPriceInterface {
        $defaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $vat = $quantifiedProduct->getProduct()->getVatForDomain($domainId);

        $basePrice = $this->giftPlanSettingFacade->calculateBaseGiftPrice($domainId, $vat);
        $totalPriceWithVat = $this->getTotalPriceWithVat($quantifiedProduct, $basePrice);
        $totalPriceVatAmount = $this->getTotalPriceVatAmountForInputPriceWithVat($totalPriceWithVat, $vat, $defaultCurrency);
        $totalPriceWithoutVat = $this->getTotalPriceWithoutVatForInputPriceWithVat($totalPriceWithVat, $totalPriceVatAmount);

        $totalPrice = new Price($totalPriceWithoutVat, $totalPriceWithVat);

        return new QuantifiedItemPrice($basePrice, $totalPrice, $vat);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $totalPriceWithVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $totalPriceVatAmount
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected function getTotalPriceWithoutVatForInputPriceWithVat(
        Money $totalPriceWithVat,
        Money $totalPriceVatAmount,
    ): Money {
        return $totalPriceWithVat->subtract($totalPriceVatAmount);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $unitPrice
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected function getTotalPriceWithoutVatForInputPriceWithoutVat(
        QuantifiedProduct $quantifiedProduct,
        PriceInterface $unitPrice,
    ): Money {
        return $unitPrice->getPriceWithoutVat()->multiply($quantifiedProduct->getQuantity());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $unitPrice
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected function getTotalPriceWithVat(
        QuantifiedProduct $quantifiedProduct,
        PriceInterface $unitPrice,
    ): Money {
        return $unitPrice->getPriceWithVat()->multiply($quantifiedProduct->getQuantity());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $totalPriceWithVat
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected function getTotalPriceVatAmountForInputPriceWithVat(
        Money $totalPriceWithVat,
        Vat $vat,
        Currency $currency,
    ): Money {
        return $this->priceCalculation->getVatAmountByPriceWithVat($totalPriceWithVat, $vat, $currency);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPriceInterface[]
     */
    public function calculatePrices(
        array $quantifiedProducts,
        int $domainId,
        ?CustomerUser $customerUser = null,
    ): array {
        $quantifiedItemsPrices = [];

        foreach ($quantifiedProducts as $quantifiedItemIndex => $quantifiedProduct) {
            $quantifiedItemsPrices[$quantifiedItemIndex] = $this->calculatePrice(
                $quantifiedProduct,
                $domainId,
                $customerUser,
            );
        }

        return $quantifiedItemsPrices;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPricesResult
     */
    public function calculateQuantifiedBasicAndSellingPrice(
        QuantifiedProduct $quantifiedProduct,
        int $domainId,
        ?CustomerUser $customerUser,
    ): QuantifiedProductPricesResult {
        $product = $quantifiedProduct->getProduct();

        $prices = $this->productPriceCalculationForCustomerUser->calculatePricesForCustomerUserAndDomainId(
            $product,
            $domainId,
            $customerUser,
        );

        $basicTotalPrice = $this->calculateTotalPrice(
            $quantifiedProduct,
            $prices->basicProductPrice,
            $product,
            $domainId,
        );
        $sellingTotalPrice = $this->calculateTotalPrice(
            $quantifiedProduct,
            $prices->sellingProductPrice,
            $product,
            $domainId,
        );

        $vat = $product->getVatForDomain($domainId);

        return new QuantifiedProductPricesResult(
            new QuantifiedItemPrice($prices->basicProductPrice->getPrice(), $basicTotalPrice, $vat),
            new QuantifiedItemPrice($prices->sellingProductPrice->getPrice(), $sellingTotalPrice, $vat),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface $productPrice
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    protected function calculateTotalPrice(
        QuantifiedProduct $quantifiedProduct,
        ProductPriceInterface $productPrice,
        Product $product,
        int $domainId,
    ): Price {
        switch ($this->pricingSetting->getInputPriceType()) {
            case PricingSetting::PRICE_TYPE_WITH_VAT:
                $totalPriceWithVat = $this->getTotalPriceWithVat($quantifiedProduct, $productPrice->getPrice());
                $totalPriceVatAmount = $this->getTotalPriceVatAmountForInputPriceWithVat($totalPriceWithVat, $product->getVatForDomain($domainId), $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId));
                $totalPriceWithoutVat = $this->getTotalPriceWithoutVatForInputPriceWithVat($totalPriceWithVat, $totalPriceVatAmount);

                break;
            case PricingSetting::PRICE_TYPE_WITHOUT_VAT:
                $totalPriceWithoutVat = $this->getTotalPriceWithoutVatForInputPriceWithoutVat($quantifiedProduct, $productPrice->getPrice());
                $totalPriceWithVat = $this->getTotalPriceWithVat($quantifiedProduct, $productPrice->getPrice());

                break;
            default:
                throw new InvalidInputPriceTypeException();
        }

        return new Price($totalPriceWithoutVat, $totalPriceWithVat);
    }
}
