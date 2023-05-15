<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\InputPriceCalculation;

class ProductInputPriceRecalculator
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation $basePriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\InputPriceCalculation $inputPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        protected readonly BasePriceCalculation $basePriceCalculation,
        protected readonly InputPriceCalculation $inputPriceCalculation,
        protected readonly CurrencyFacade $currencyFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice $productManualInputPrice
     * @param int $inputPriceType
     * @param string $newVatPercent
     */
    public function recalculateInputPriceForNewVatPercent(
        ProductManualInputPrice $productManualInputPrice,
        int $inputPriceType,
        string $newVatPercent,
    ): void {
        if ($productManualInputPrice->getInputPrice() !== null) {
            $domainId = $productManualInputPrice->getPricingGroup()->getDomainId();
            $defaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

            $basePriceForPricingGroup = $this->basePriceCalculation->calculateBasePriceRoundedByCurrency(
                $productManualInputPrice->getInputPrice(),
                $inputPriceType,
                $productManualInputPrice->getProduct()->getVatForDomain($domainId),
                $defaultCurrency,
            );
            $inputPriceForPricingGroup = $this->inputPriceCalculation->getInputPrice(
                $inputPriceType,
                $basePriceForPricingGroup->getPriceWithVat(),
                $newVatPercent,
            );
            $productManualInputPrice->setInputPrice($inputPriceForPricingGroup);
        }
    }
}
