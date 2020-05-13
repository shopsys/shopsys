<?php

declare(strict_types=1);

namespace App\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation as BaseProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Product;

/**
 * @method \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice calculatePrice(\App\Model\Product\Product $product, int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @method \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice calculateMainVariantPrice(\App\Model\Product\Product $mainVariant, int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @property \App\Model\Product\ProductRepository $productRepository
 * @method __construct(\Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation $basePriceCalculation, \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting, \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceRepository $productManualInputPriceRepository, \App\Model\Product\ProductRepository $productRepository, \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade)
 */
class ProductPriceCalculation extends BaseProductPriceCalculation
{
    /**
     * @param \App\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    protected function calculateProductPriceForPricingGroup(Product $product, PricingGroup $pricingGroup)
    {
        $domainId = $pricingGroup->getDomainId();
        $inputPrice = $product->getLowPriceWithVat($domainId) ?? Money::zero();
        $defaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        $basePrice = $this->basePriceCalculation->calculateBasePriceRoundedByCurrency(
            $inputPrice,
            $this->pricingSetting->getInputPriceType(),
            $product->getVatForDomain($domainId),
            $defaultCurrency
        );

        return new ProductPrice($basePrice, false);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @param int $multiplier
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function calculateProductNonSellingPrice(Product $product, int $domainId, int $multiplier = 1): ProductPrice
    {
        $highPriceWithVat = $product->getHighPriceWithVat($domainId) ?? Money::zero();
        if ($multiplier !== 1) {
            $highPriceWithVat = $highPriceWithVat->multiply($multiplier);
        }

        $defaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        $highPrice = $this->basePriceCalculation->calculateBasePriceRoundedByCurrency(
            $highPriceWithVat,
            PricingSetting::INPUT_PRICE_TYPE_WITH_VAT,
            $product->getVatForDomain($domainId),
            $defaultCurrency
        );

        return new ProductPrice($highPrice, false);
    }
}
