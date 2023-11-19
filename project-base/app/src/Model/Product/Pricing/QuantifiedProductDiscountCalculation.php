<?php

declare(strict_types=1);

namespace App\Model\Product\Pricing;

use App\Model\Order\PromoCode\PromoCode;
use App\Model\Order\PromoCode\PromoCodeApplicableProductsTotalPriceCalculator;
use App\Model\Order\PromoCode\PromoCodeLimit;
use App\Model\Order\PromoCode\PromoCodeLimitResolver;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductDiscountCalculation as BaseQuantifiedProductDiscountCalculation;

class QuantifiedProductDiscountCalculation extends BaseQuantifiedProductDiscountCalculation
{
    /**
     * @param \App\Model\Order\PromoCode\PromoCodeLimitResolver $promoCodeLimitResolver
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation $priceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Rounding $rounding
     * @param \App\Model\Order\PromoCode\PromoCodeApplicableProductsTotalPriceCalculator $totalPriceCalculator
     */
    public function __construct(
        private PromoCodeLimitResolver $promoCodeLimitResolver,
        PriceCalculation $priceCalculation,
        Rounding $rounding,
        private PromoCodeApplicableProductsTotalPriceCalculator $totalPriceCalculator,
    ) {
        parent::__construct($priceCalculation, $rounding);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices
     * @param \App\Model\Order\PromoCode\PromoCode[] $promoCodePerProduct
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    public function calculateDiscountsPerProductRoundedByCurrency(
        array $quantifiedProducts,
        array $quantifiedItemsPrices,
        array $promoCodePerProduct,
        Currency $currency,
    ): array {
        $discountsPerProduct = $this->prefillNullDiscounts($quantifiedProducts);
        $applicablePromoCodeProductsCount = count($promoCodePerProduct);

        if ($applicablePromoCodeProductsCount < 1) {
            return $discountsPerProduct;
        }

        $promoCode = reset($promoCodePerProduct);
        $promoCodeLimit = $this->promoCodeLimitResolver->getLimitByPromoCode($promoCode, $quantifiedProducts);

        if ($promoCodeLimit === null) {
            return $discountsPerProduct;
        }

        if ($promoCode->getDiscountType() === PromoCode::DISCOUNT_TYPE_PERCENT) {
            return $this->calculateDiscountPercentPrices(
                $discountsPerProduct,
                $quantifiedProducts,
                $promoCodePerProduct,
                $quantifiedItemsPrices,
                $promoCode,
                $promoCodeLimit,
                $currency,
            );
        }

        if ($promoCode->getDiscountType() === PromoCode::DISCOUNT_TYPE_NOMINAL) {
            return $this->calculateDiscountNominalPrices(
                $discountsPerProduct,
                $quantifiedProducts,
                $promoCodePerProduct,
                $quantifiedItemsPrices,
                $promoCodeLimit,
                $currency,
            );
        }

        return $discountsPerProduct;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $quantifiedItemsDiscounts
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]|null[]
     */
    public function calculateDiscountPricesPerProductRoundedByCurrency(
        array $quantifiedItemsPrices,
        array $quantifiedItemsDiscounts,
        Currency $currency,
    ): array {
        $quantifiedItemsDiscountPrices = [];

        foreach ($quantifiedItemsPrices as $quantifiedItemIndex => $quantifiedItemPrice) {
            $discount = $quantifiedItemsDiscounts[$quantifiedItemIndex] ?? null;

            if ($discount !== null) {
                $quantifiedItemsDiscountPrices[$quantifiedItemIndex] = $this->calculateDiscountPriceRoundedByCurrency(
                    $quantifiedItemPrice,
                    $discount,
                    $currency,
                );
            } else {
                $quantifiedItemsDiscountPrices[$quantifiedItemIndex] = null;
            }
        }

        return $quantifiedItemsDiscountPrices;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice $quantifiedItemPrice
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \App\Model\Order\PromoCode\PromoCodeLimit $promoCodeLimit
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    private function calculateRoundedDiscountByPromoCode(
        QuantifiedItemPrice $quantifiedItemPrice,
        PromoCode $promoCode,
        PromoCodeLimit $promoCodeLimit,
        Currency $currency,
        QuantifiedProduct $quantifiedProduct,
    ): ?Price {
        $percent = $promoCodeLimit->getDiscount();

        if ($percent === null) {
            return null;
        }

        return $this->calculateDiscountRoundedByCurrency($quantifiedItemPrice, $percent, $currency);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice $quantifiedItemPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $discount
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    private function calculateDiscountPriceRoundedByCurrency(
        QuantifiedItemPrice $quantifiedItemPrice,
        Price $discount,
        Currency $currency,
    ): ?Price {
        $vat = $quantifiedItemPrice->getVat();
        $priceWithVat = $this->rounding->roundPriceWithVatByCurrency(
            $quantifiedItemPrice->getTotalPrice()->getPriceWithVat()->subtract($discount->getPriceWithVat()),
            $currency,
        );

        if ($priceWithVat->isZero()) {
            return null;
        }

        $priceVatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($priceWithVat, $vat);
        $priceWithoutVat = $priceWithVat->subtract($priceVatAmount);

        return new Price($priceWithoutVat, $priceWithVat);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice $quantifiedItemPrice
     * @param \App\Model\Order\PromoCode\PromoCodeLimit $promoCodeLimit
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $totalApplicableProductsPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private function calculateNominalDiscountRoundedByCurrency(
        QuantifiedItemPrice $quantifiedItemPrice,
        PromoCodeLimit $promoCodeLimit,
        Money $totalApplicableProductsPrice,
        Currency $currency,
    ): Price {
        $productPriceWithVat = $quantifiedItemPrice
            ->getTotalPrice()
            ->getPriceWithVat();

        $totalDiscount = Money::create($promoCodeLimit->getDiscount());
        $productDiscountPercent = $totalDiscount
            ->divide($totalApplicableProductsPrice->getAmount(), 6)
            ->getAmount();

        $productDiscountWithVat = $this->rounding->roundPriceWithVatByCurrency(
            $productPriceWithVat->multiply($productDiscountPercent),
            $currency,
        );

        $productVat = $quantifiedItemPrice->getVat();
        $productVatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($productDiscountWithVat, $productVat);
        $productDiscountWithoutVat = $this->rounding->roundPriceWithoutVat(
            $productDiscountWithVat->subtract($productVatAmount),
        );

        return new Price($productDiscountWithoutVat, $productDiscountWithVat);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @return array<int|string, null>
     */
    private function prefillNullDiscounts(array $quantifiedProducts): array
    {
        return array_fill_keys(
            array_keys($quantifiedProducts),
            null,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $discountsPerProduct
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \App\Model\Order\PromoCode\PromoCode[] $promoCodePerProduct
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \App\Model\Order\PromoCode\PromoCodeLimit $promoCodeLimit
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    private function calculateDiscountPercentPrices(
        array $discountsPerProduct,
        array $quantifiedProducts,
        array $promoCodePerProduct,
        array $quantifiedItemsPrices,
        PromoCode $promoCode,
        PromoCodeLimit $promoCodeLimit,
        Currency $currency,
    ): array {
        foreach ($quantifiedProducts as $quantifiedProductIndex => $quantifiedProduct) {
            $productId = $quantifiedProduct->getProduct()->getId();

            if (!array_key_exists($productId, $promoCodePerProduct)) {
                continue;
            }

            $quantifiedItemPrice = $quantifiedItemsPrices[$quantifiedProductIndex];
            $discountsPerProduct[$quantifiedProductIndex] = $this->calculateRoundedDiscountByPromoCode(
                $quantifiedItemPrice,
                $promoCode,
                $promoCodeLimit,
                $currency,
                $quantifiedProduct,
            );
        }

        return $discountsPerProduct;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $discountsPerProduct
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \App\Model\Order\PromoCode\PromoCode[] $promoCodePerProduct
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices
     * @param \App\Model\Order\PromoCode\PromoCodeLimit $promoCodeLimit
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    private function calculateDiscountNominalPrices(
        array $discountsPerProduct,
        array $quantifiedProducts,
        array $promoCodePerProduct,
        array $quantifiedItemsPrices,
        PromoCodeLimit $promoCodeLimit,
        Currency $currency,
    ): array {
        $cartPromoCodeApplicableProductsTotalPrice = $this->totalPriceCalculator->calculateTotalPrice(
            $quantifiedProducts,
        );
        $sumPriceWithVat = $cartPromoCodeApplicableProductsTotalPrice->getPriceWithVat();

        foreach ($quantifiedProducts as $quantifiedProductIndex => $quantifiedProduct) {
            $productId = $quantifiedProduct->getProduct()->getId();

            if (!array_key_exists($productId, $promoCodePerProduct)) {
                continue;
            }

            $quantifiedItemPrice = $quantifiedItemsPrices[$quantifiedProductIndex];
            $discountsPerProduct[$quantifiedProductIndex] = $this->calculateNominalDiscountRoundedByCurrency(
                $quantifiedItemPrice,
                $promoCodeLimit,
                $sumPriceWithVat,
                $currency,
            );
        }

        return $discountsPerProduct;
    }
}
