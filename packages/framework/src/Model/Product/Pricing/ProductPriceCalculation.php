<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidArgumentException;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class ProductPriceCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation $basePriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceRepository $productManualInputPriceRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        protected readonly BasePriceCalculation $basePriceCalculation,
        protected readonly PricingSetting $pricingSetting,
        protected readonly ProductManualInputPriceRepository $productManualInputPriceRepository,
        protected readonly ProductRepository $productRepository,
        protected readonly CurrencyFacade $currencyFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function calculatePrice(Product $product, $domainId, PricingGroup $pricingGroup)
    {
        if ($product->isMainVariant()) {
            return $this->calculateMainVariantPrice($product, $domainId, $pricingGroup);
        }

        return $this->calculateProductPriceForPricingGroup($product, $pricingGroup);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $mainVariant
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    protected function calculateMainVariantPrice(Product $mainVariant, $domainId, PricingGroup $pricingGroup)
    {
        $variants = $this->productRepository->getAllSellableVariantsByMainVariant(
            $mainVariant,
            $domainId,
            $pricingGroup,
        );

        if (count($variants) === 0) {
            $message = 'Main variant ID = ' . $mainVariant->getId() . ' has no sellable variants.';

            throw new MainVariantPriceCalculationException($message);
        }

        $variantPrices = [];

        foreach ($variants as $variant) {
            $variantPrices[] = $this->calculatePrice($variant, $domainId, $pricingGroup);
        }

        $minVariantPrice = $this->getMinimumPriceByPriceWithoutVat($variantPrices);
        $from = $this->arePricesDifferent($variantPrices);

        return new ProductPrice($minVariantPrice, $from);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    protected function calculateProductPriceForPricingGroup(Product $product, PricingGroup $pricingGroup)
    {
        $manualInputPrice = $this->productManualInputPriceRepository->findByProductAndPricingGroup(
            $product,
            $pricingGroup,
        );

        if ($manualInputPrice !== null) {
            $inputPrice = $manualInputPrice->getInputPrice() ?? Money::zero();
        } else {
            $inputPrice = Money::zero();
        }

        $domainId = $pricingGroup->getDomainId();
        $defaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        $basePrice = $this->basePriceCalculation->calculateBasePriceRoundedByCurrency(
            $inputPrice,
            $this->pricingSetting->getInputPriceType(),
            $product->getVatForDomain($domainId),
            $defaultCurrency,
        );

        return new ProductPrice($basePrice, false);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $prices
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getMinimumPriceByPriceWithoutVat(array $prices)
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

        return $minimumPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $prices
     * @return bool
     */
    public function arePricesDifferent(array $prices)
    {
        if (count($prices) === 0) {
            throw new InvalidArgumentException('Array can not be empty.');
        }

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Price $firstPrice */
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
