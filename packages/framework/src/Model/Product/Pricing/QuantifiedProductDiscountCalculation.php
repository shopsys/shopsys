<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeApplicableProductsTotalPriceCalculator;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitResolver;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;

class QuantifiedProductDiscountCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitResolver $promoCodeLimitResolver
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation $priceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Rounding $rounding
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeApplicableProductsTotalPriceCalculator $promoCodeApplicableProductsTotalPriceCalculator
     */
    public function __construct(
        protected readonly PromoCodeLimitResolver $promoCodeLimitResolver,
        protected readonly PriceCalculation $priceCalculation,
        protected readonly Rounding $rounding,
        protected readonly PromoCodeApplicableProductsTotalPriceCalculator $promoCodeApplicableProductsTotalPriceCalculator,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[] $promoCodePerProduct
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
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @return array
     */
    protected function prefillNullDiscounts(array $quantifiedProducts): array
    {
        return array_fill_keys(
            array_keys($quantifiedProducts),
            null,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $discountsPerProduct
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[] $promoCodePerProduct
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit $promoCodeLimit
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    protected function calculateDiscountPercentPrices(
        array $discountsPerProduct,
        array $quantifiedProducts,
        array $promoCodePerProduct,
        array $quantifiedItemsPrices,
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
                $promoCodeLimit,
                $currency,
            );
        }

        return $discountsPerProduct;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $discountsPerProduct
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[] $promoCodePerProduct
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit $promoCodeLimit
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    protected function calculateDiscountNominalPrices(
        array $discountsPerProduct,
        array $quantifiedProducts,
        array $promoCodePerProduct,
        array $quantifiedItemsPrices,
        PromoCodeLimit $promoCodeLimit,
        Currency $currency,
    ): array {
        $cartPromoCodeApplicableProductsTotalPrice = $this->promoCodeApplicableProductsTotalPriceCalculator->calculateTotalPrice(
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice $quantifiedItemPrice
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit $promoCodeLimit
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    protected function calculateRoundedDiscountByPromoCode(
        QuantifiedItemPrice $quantifiedItemPrice,
        PromoCodeLimit $promoCodeLimit,
        Currency $currency,
    ): ?Price {
        $percent = $promoCodeLimit->getDiscount();

        if ($percent === null) {
            return null;
        }

        return $this->calculateDiscountRoundedByCurrency($quantifiedItemPrice, $percent, $currency);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice $quantifiedItemPrice
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit $promoCodeLimit
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $totalApplicableProductsPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    protected function calculateNominalDiscountRoundedByCurrency(
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
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice $quantifiedItemPrice
     * @param string $discountPercent
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    protected function calculateDiscountRoundedByCurrency(
        QuantifiedItemPrice $quantifiedItemPrice,
        string $discountPercent,
        Currency $currency,
    ): ?Price {
        $vat = $quantifiedItemPrice->getVat();
        $multiplier = (string)((float)$discountPercent / 100);
        $priceWithVat = $this->rounding->roundPriceWithVatByCurrency(
            $quantifiedItemPrice->getTotalPrice()->getPriceWithVat()->multiply($multiplier),
            $currency,
        );

        if ($priceWithVat->isZero()) {
            return null;
        }

        $priceVatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($priceWithVat, $vat);
        $priceWithoutVat = $priceWithVat->subtract($priceVatAmount);

        return new Price($priceWithoutVat, $priceWithVat);
    }
}
