<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\PricingService;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class ProductPriceCalculation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation
     */
    private $basePriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting
     */
    private $pricingSetting;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceRepository
     */
    private $productManualInputPriceRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private $currencyFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PricingService
     */
    private $pricingService;

    public function __construct(
        BasePriceCalculation $basePriceCalculation,
        PricingSetting $pricingSetting,
        ProductManualInputPriceRepository $productManualInputPriceRepository,
        CurrencyFacade $currencyFacade,
        ProductRepository $productRepository,
        PricingService $pricingService
    ) {
        $this->pricingSetting = $pricingSetting;
        $this->basePriceCalculation = $basePriceCalculation;
        $this->productManualInputPriceRepository = $productManualInputPriceRepository;
        $this->currencyFacade = $currencyFacade;
        $this->productRepository = $productRepository;
        $this->pricingService = $pricingService;
    }

    public function calculatePrice(Product $product, int $domainId, PricingGroup $pricingGroup): \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
    {
        if ($product->isMainVariant()) {
            return $this->calculateMainVariantPrice($product, $domainId, $pricingGroup);
        }

        $priceCalculationType = $product->getPriceCalculationType();
        if ($priceCalculationType === Product::PRICE_CALCULATION_TYPE_AUTO) {
            return $this->calculateProductPriceForPricingGroupAuto($product, $pricingGroup, $domainId);
        } elseif ($priceCalculationType === Product::PRICE_CALCULATION_TYPE_MANUAL) {
            return $this->calculateProductPriceForPricingGroupManual($product, $pricingGroup);
        } else {
            throw new \Shopsys\FrameworkBundle\Model\Product\Exception\InvalidPriceCalculationTypeException(
                $priceCalculationType
            );
        }
    }

    private function calculateMainVariantPrice(Product $mainVariant, int $domainId, PricingGroup $pricingGroup): \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
    {
        $variants = $this->productRepository->getAllSellableVariantsByMainVariant(
            $mainVariant,
            $domainId,
            $pricingGroup
        );
        if (count($variants) === 0) {
            $message = 'Main variant ID = ' . $mainVariant->getId() . ' has no sellable variants.';
            throw new \Shopsys\FrameworkBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException($message);
        }

        $variantPrices = [];
        foreach ($variants as $variant) {
            $variantPrices[] = $this->calculatePrice($variant, $domainId, $pricingGroup);
        }

        $minVariantPrice = $this->pricingService->getMinimumPriceByPriceWithoutVat($variantPrices);
        $from = $this->pricingService->arePricesDifferent($variantPrices);

        return new ProductPrice($minVariantPrice, $from);
    }

    private function calculateBasePrice(string $inputPrice, Vat $vat): \Shopsys\FrameworkBundle\Model\Pricing\Price
    {
        return $this->basePriceCalculation->calculateBasePrice(
            $inputPrice,
            $this->pricingSetting->getInputPriceType(),
            $vat
        );
    }

    private function calculateProductPriceForPricingGroupManual(Product $product, PricingGroup $pricingGroup): \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
    {
        $manualInputPrice = $this->productManualInputPriceRepository->findByProductAndPricingGroup($product, $pricingGroup);
        if ($manualInputPrice !== null) {
            $inputPrice = $manualInputPrice->getInputPrice();
        } else {
            $inputPrice = 0;
        }
        $basePrice = $this->calculateBasePrice($inputPrice, $product->getVat());

        return new ProductPrice($basePrice, false);
    }

    private function calculateProductPriceForPricingGroupAuto(Product $product, PricingGroup $pricingGroup, int $domainId): \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
    {
        $basePrice = $this->calculateBasePrice($product->getPrice(), $product->getVat());

        $price = $this->basePriceCalculation->applyCoefficients(
            $basePrice,
            $product->getVat(),
            [$pricingGroup->getCoefficient(), $this->getDomainDefaultCurrencyReversedExchangeRate($domainId)]
        );

        return new ProductPrice($price, false);
    }

    private function getDomainDefaultCurrencyReversedExchangeRate(int $domainId): string
    {
        $domainDefaultCurrencyId = $this->pricingSetting->getDomainDefaultCurrencyIdByDomainId($domainId);
        $currency = $this->currencyFacade->getById($domainDefaultCurrencyId);

        return $currency->getReversedExchangeRate();
    }
}
