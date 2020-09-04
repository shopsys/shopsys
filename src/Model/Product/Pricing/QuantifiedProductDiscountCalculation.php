<?php

declare(strict_types=1);

namespace App\Model\Product\Pricing;

use App\Model\Cart\CartFacade;
use App\Model\Order\Item\QuantifiedItemPrice as AppQuantifiedItemPrice;
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
     * @var \App\Model\Order\PromoCode\PromoCodeLimitResolver
     */
    private PromoCodeLimitResolver $promoCodeLimitResolver;

    /**
     * @var \App\Model\Cart\CartFacade
     */
    private CartFacade $cartFacade;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeApplicableProductsTotalPriceCalculator
     */
    private PromoCodeApplicableProductsTotalPriceCalculator $totalPriceCalculator;

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeLimitResolver $promoCodeLimitResolver
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation $priceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Rounding $rounding
     * @param \App\Model\Cart\CartFacade $cartFacade
     * @param \App\Model\Order\PromoCode\PromoCodeApplicableProductsTotalPriceCalculator $totalPriceCalculator
     */
    public function __construct(
        PromoCodeLimitResolver $promoCodeLimitResolver,
        PriceCalculation $priceCalculation,
        Rounding $rounding,
        CartFacade $cartFacade,
        PromoCodeApplicableProductsTotalPriceCalculator $totalPriceCalculator
    ) {
        parent::__construct($priceCalculation, $rounding);

        $this->promoCodeLimitResolver = $promoCodeLimitResolver;
        $this->cartFacade = $cartFacade;
        $this->totalPriceCalculator = $totalPriceCalculator;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $productTypeQuantifiedProducts
     * @param \App\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices
     * @param \App\Model\Order\PromoCode\PromoCode[] $promoCodePerProduct
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    public function calculateDiscountsPerProductRoundedByCurrency(
        array $productTypeQuantifiedProducts,
        array $quantifiedItemsPrices,
        array $promoCodePerProduct,
        Currency $currency
    ): array {
        $discountsPerProduct = $this->prefillNullDiscounts($productTypeQuantifiedProducts);
        $applicablePromoCodeProductsCount = count($promoCodePerProduct);
        if ($applicablePromoCodeProductsCount < 1) {
            return $discountsPerProduct;
        }

        $promoCode = reset($promoCodePerProduct);
        $cartQuantifiedProducts = $this->cartFacade->getQuantifiedProductsOfCurrentCustomer();
        $promoCodeLimit = $this->promoCodeLimitResolver->getLimitByPromoCode($promoCode, $cartQuantifiedProducts);
        if ($promoCodeLimit === null) {
            return $discountsPerProduct;
        }

        if ($promoCode->getDiscountType() === PromoCode::DISCOUNT_TYPE_PERCENT) {
            return $this->calculateDiscountPercentPrices(
                $discountsPerProduct,
                $productTypeQuantifiedProducts,
                $promoCodePerProduct,
                $quantifiedItemsPrices,
                $promoCode,
                $promoCodeLimit,
                $currency
            );
        }

        if ($promoCode->getDiscountType() === PromoCode::DISCOUNT_TYPE_NOMINAL) {
            return $this->calculateDiscountNominalPrices(
                $discountsPerProduct,
                $cartQuantifiedProducts,
                $productTypeQuantifiedProducts,
                $promoCodePerProduct,
                $quantifiedItemsPrices,
                $promoCode,
                $promoCodeLimit,
                $currency
            );
        }

        return $discountsPerProduct;
    }

    /**
     * @param \App\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $quantifiedItemsDiscounts
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]|null[]
     */
    public function calculateDiscountPricesPerProductRoundedByCurrency(array $quantifiedItemsPrices, array $quantifiedItemsDiscounts, Currency $currency): array
    {
        $quantifiedItemsDiscountPrices = [];
        foreach ($quantifiedItemsPrices as $quantifiedItemIndex => $quantifiedItemPrice) {
            $discount = $quantifiedItemsDiscounts[$quantifiedItemIndex] ?? null;

            if ($discount !== null) {
                $quantifiedItemsDiscountPrices[$quantifiedItemIndex] = $this->calculateDiscountPriceRoundedByCurrency(
                    $quantifiedItemPrice,
                    $discount,
                    $currency
                );
            } else {
                $quantifiedItemsDiscountPrices[$quantifiedItemIndex] = null;
            }
        }

        return $quantifiedItemsDiscountPrices;
    }

    /**
     * @param \App\Model\Order\Item\QuantifiedItemPrice $quantifiedItemPrice
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
        QuantifiedProduct $quantifiedProduct
    ): ?Price {
        $percent = $promoCodeLimit->getDiscount();
        if ($percent === null) {
            return null;
        }

        if ($promoCode->isApplyOnSecondProduct()) {
            return $this->calculateDiscountOnSecondProductRoundedByCurrency(
                $quantifiedProduct,
                $quantifiedItemPrice,
                $percent,
                $currency
            );
        }

        return $this->calculateDiscountRoundedByCurrency($quantifiedItemPrice, $percent, $currency);
    }

    /**
     * @param \App\Model\Order\Item\QuantifiedItemPrice $quantifiedItemPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $discount
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    private function calculateDiscountPriceRoundedByCurrency(
        QuantifiedItemPrice $quantifiedItemPrice,
        Price $discount,
        Currency $currency
    ): ?Price {
        $vat = $quantifiedItemPrice->getVat();
        $priceWithVat = $this->rounding->roundPriceWithVatByCurrency(
            $quantifiedItemPrice->getTotalPrice()->getPriceWithVat()->subtract($discount->getPriceWithVat()),
            $currency
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
        Currency $currency
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
            $currency
        );

        $productVat = $quantifiedItemPrice->getVat();
        $productVatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($productDiscountWithVat, $productVat);
        $productDiscountWithoutVat = $this->rounding->roundPriceWithoutVat(
            $productDiscountWithVat->subtract($productVatAmount)
        );

        return new Price($productDiscountWithoutVat, $productDiscountWithVat);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param \App\Model\Order\Item\QuantifiedItemPrice $quantifiedItemPrice
     * @param string $percent
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    private function calculateDiscountOnSecondProductRoundedByCurrency(
        QuantifiedProduct $quantifiedProduct,
        AppQuantifiedItemPrice $quantifiedItemPrice,
        string $percent,
        Currency $currency
    ): ?Price {
        $quantity = $quantifiedProduct->getQuantity();
        $unitHighPriceWithVat = $quantifiedItemPrice->getUnitHighPrice()->getPriceWithVat();

        $discountedProductQuantity = intdiv($quantity, 2);
        $discountMultiplier = (string)($percent / 100);
        $singleDiscountFromHighUnitPrice = $unitHighPriceWithVat->multiply($discountMultiplier);

        $singleDiscountWithVat = $this->rounding->roundPriceWithVatByCurrency(
            $singleDiscountFromHighUnitPrice,
            $currency
        );
        $discountWithVat = $singleDiscountWithVat->multiply($discountedProductQuantity);

        if ($discountWithVat->isZero()) {
            return null;
        }

        $vat = $quantifiedItemPrice->getVat();
        $discountVatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($discountWithVat, $vat);
        $discountWithoutVat = $discountWithVat->subtract($discountVatAmount);

        return new Price($discountWithoutVat, $discountWithVat);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $productTypeQuantifiedProducts
     * @return array
     */
    private function prefillNullDiscounts(array $productTypeQuantifiedProducts): array
    {
        return array_fill_keys(
            array_keys($productTypeQuantifiedProducts),
            null
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $discountsPerProduct
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $productTypeQuantifiedProducts
     * @param \App\Model\Order\PromoCode\PromoCode[] $promoCodePerProduct
     * @param \App\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \App\Model\Order\PromoCode\PromoCodeLimit $promoCodeLimit
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     *
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    private function calculateDiscountPercentPrices(
        array $discountsPerProduct,
        array $productTypeQuantifiedProducts,
        array $promoCodePerProduct,
        array $quantifiedItemsPrices,
        PromoCode $promoCode,
        PromoCodeLimit $promoCodeLimit,
        Currency $currency
    ): array {
        foreach ($productTypeQuantifiedProducts as $quantifiedProductIndex => $quantifiedProduct) {
            $productId = $quantifiedProduct->getProduct()->getId();
            if (array_key_exists($productId, $promoCodePerProduct)) {
                $quantifiedItemPrice = $quantifiedItemsPrices[$quantifiedProductIndex];
                $discountsPerProduct[$quantifiedProductIndex] = $this->calculateRoundedDiscountByPromoCode(
                    $quantifiedItemPrice,
                    $promoCode,
                    $promoCodeLimit,
                    $currency,
                    $quantifiedProduct
                );
            }
        }

        return $discountsPerProduct;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $discountsPerProduct
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $cartQuantifiedProducts ,
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $productTypeQuantifiedProducts
     * @param \App\Model\Order\PromoCode\PromoCode[] $promoCodePerProduct
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \App\Model\Order\PromoCode\PromoCodeLimit $promoCodeLimit
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     *
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    private function calculateDiscountNominalPrices(
        array $discountsPerProduct,
        array $cartQuantifiedProducts,
        array $productTypeQuantifiedProducts,
        array $promoCodePerProduct,
        array $quantifiedItemsPrices,
        PromoCode $promoCode,
        PromoCodeLimit $promoCodeLimit,
        Currency $currency
    ): array {
        $cartPromoCodeApplicableProductsTotalPrice = $this->totalPriceCalculator->calculateTotalPrice(
            $promoCode,
            $cartQuantifiedProducts
        );
        $sumPriceWithVat = $cartPromoCodeApplicableProductsTotalPrice->getPriceWithVat();
        foreach ($productTypeQuantifiedProducts as $quantifiedProductIndex => $quantifiedProduct) {
            $productId = $quantifiedProduct->getProduct()->getId();
            if (array_key_exists($productId, $promoCodePerProduct)) {
                $quantifiedItemPrice = $quantifiedItemsPrices[$quantifiedProductIndex];
                $discountsPerProduct[$quantifiedProductIndex] = $this->calculateNominalDiscountRoundedByCurrency(
                    $quantifiedItemPrice,
                    $promoCodeLimit,
                    $sumPriceWithVat,
                    $currency
                );
            }
        }

        return $discountsPerProduct;
    }
}
