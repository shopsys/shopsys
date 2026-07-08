<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrentCurrencyProvider;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidArgumentException;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\ProductPricesMulticurrencyModeProvider;
use Shopsys\FrameworkBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;

class ProductPriceCalculation
{
    public function __construct(
        protected readonly BasePriceCalculation $basePriceCalculation,
        protected readonly PricingSetting $pricingSetting,
        protected readonly ProductManualInputPriceRepository $productManualInputPriceRepository,
        protected readonly ProductRepository $productRepository,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly CurrentCurrencyProvider $currentCurrencyProvider,
        protected readonly ProductPricesMulticurrencyModeProvider $productPricesMulticurrencyModeProvider,
    ) {
    }

    public function calculatePrice(Product $product, int $domainId, PricingGroup $pricingGroup): ProductPriceInterface
    {
        if ($product->isMainVariant()) {
            return $this->calculateMainVariantPrice($product, $domainId, $pricingGroup);
        }

        return $this->calculateProductPriceForPricingGroup($product, $pricingGroup);
    }

    protected function calculateMainVariantPrice(
        Product $mainVariant,
        int $domainId,
        PricingGroup $pricingGroup,
    ): ProductPriceInterface {
        $variants = $this->productRepository->getAllSellableVariantsByMainVariant(
            $mainVariant,
            $domainId,
            $pricingGroup,
        );

        $variants = array_filter(
            $variants,
            static fn (Product $variant) => $variant->getProductType() !== ProductTypeEnum::TYPE_INQUIRY,
        );

        if (count($variants) === 0) {
            $message = 'Main variant ID = ' . $mainVariant->getId() . ' has no sellable variants.';

            throw new MainVariantPriceCalculationException($message);
        }

        $variantPrices = [];

        foreach ($variants as $variant) {
            $variantPrices[] = $this->calculateProductPriceForPricingGroup($variant, $pricingGroup)->getPrice();
        }

        $minVariantPrice = $this->getMinimumPriceByPriceWithoutVat($variantPrices);
        $from = $this->arePricesDifferent($variantPrices);

        return new ProductPrice($minVariantPrice, $pricingGroup, $from);
    }

    protected function calculateProductPriceForPricingGroup(
        Product $product,
        PricingGroup $pricingGroup,
    ): ProductPriceInterface {
        $domainId = $pricingGroup->getDomainId();
        $currentCurrency = $this->currentCurrencyProvider->getCurrentCurrencyOfDomain($domainId);

        $inputPrice = $this->getInputPrice($product, $pricingGroup, $currentCurrency);

        $basePrice = $this->basePriceCalculation->calculateRoundedBasePrice(
            $inputPrice,
            $this->pricingSetting->getInputPriceType(),
            $product->getVatForDomain($domainId),
            $currentCurrency->getRoundingType(),
            $currentCurrency->getRoundingPlacesPriceWithoutVat(),
            $currentCurrency,
        );

        return new ProductPrice($basePrice, $pricingGroup, false);
    }

    /**
     * In the manual multicurrency mode the input price is stored separately for every enabled currency,
     * in the calculated mode only the domain default currency price is stored and other currencies are converted by exchange rate
     */
    protected function getInputPrice(
        Product $product,
        PricingGroup $pricingGroup,
        Currency $currentCurrency,
    ): Money {
        if ($this->productPricesMulticurrencyModeProvider->isManualMode()) {
            $manualInputPrice = $this->productManualInputPriceRepository->findByProductPricingGroupAndCurrency(
                $product,
                $pricingGroup,
                $currentCurrency,
            );

            return $manualInputPrice?->getInputPrice() ?? Money::zero();
        }

        $defaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($pricingGroup->getDomainId());
        $manualInputPrice = $this->productManualInputPriceRepository->findByProductPricingGroupAndCurrency(
            $product,
            $pricingGroup,
            $defaultCurrency,
        );
        $inputPrice = $manualInputPrice?->getInputPrice() ?? Money::zero();

        if ($currentCurrency->getCode() !== $defaultCurrency->getCode()) {
            $inputPrice = $inputPrice->multiply(
                (string)$this->currencyFacade->getExchangeRateForCurrencies($defaultCurrency, $currentCurrency),
            );
        }

        return $inputPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface[] $prices
     */
    public function getMinimumPriceByPriceWithoutVat(array $prices): PriceInterface
    {
        if (count($prices) === 0) {
            throw new InvalidArgumentException('Array can not be empty.');
        }

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Price|null $minimumPrice */
        $minimumPrice = null;

        foreach ($prices as $price) {
            if (
                $minimumPrice === null
                || $price->getPriceWithoutVat()->isLessThan($minimumPrice->getPriceWithoutVat())
            ) {
                $minimumPrice = $price;
            }
        }

        if ($minimumPrice === null) {
            throw new MainVariantPriceCalculationException('Minimum price not found.');
        }

        return $minimumPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface[] $prices
     */
    public function arePricesDifferent(array $prices): bool
    {
        if (count($prices) === 0) {
            throw new InvalidArgumentException('Array can not be empty.');
        }

        $firstPrice = array_pop($prices);

        foreach ($prices as $price) {
            if (!$price->getPriceWithoutVat()->equals($firstPrice->getPriceWithoutVat())
                || !$price->getPriceWithVat()->equals($firstPrice->getPriceWithVat())
            ) {
                return true;
            }
        }

        return false;
    }
}
