<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use BadMethodCallException;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\InputPriceCalculation;

class ProductInputPriceRecalculator
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation
     */
    protected $basePriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\InputPriceCalculation
     */
    protected $inputPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade|null
     */
    protected $currencyFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation $basePriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\InputPriceCalculation $inputPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade|null $currencyFacade
     */
    public function __construct(
        BasePriceCalculation $basePriceCalculation,
        InputPriceCalculation $inputPriceCalculation,
        ?CurrencyFacade $currencyFacade = null
    ) {
        $this->basePriceCalculation = $basePriceCalculation;
        $this->inputPriceCalculation = $inputPriceCalculation;
        $this->currencyFacade = $currencyFacade;
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setCurrencyFacade(CurrencyFacade $currencyFacade): void
    {
        if ($this->currencyFacade !== null && $this->currencyFacade !== $currencyFacade) {
            throw new BadMethodCallException(sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__));
        }
        if ($this->currencyFacade === null) {
            @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.', __METHOD__), E_USER_DEPRECATED);
            $this->currencyFacade = $currencyFacade;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice $productManualInputPrice
     * @param int $inputPriceType
     * @param string $newVatPercent
     */
    public function recalculateInputPriceForNewVatPercent(
        ProductManualInputPrice $productManualInputPrice,
        int $inputPriceType,
        string $newVatPercent
    ): void {
        if ($productManualInputPrice->getInputPrice() !== null) {
            $domainId = $productManualInputPrice->getPricingGroup()->getDomainId();
            $defaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

            $basePriceForPricingGroup = $this->basePriceCalculation->calculateBasePriceRoundedByCurrency(
                $productManualInputPrice->getInputPrice(),
                $inputPriceType,
                $productManualInputPrice->getProduct()->getVat(),
                $defaultCurrency
            );
            $inputPriceForPricingGroup = $this->inputPriceCalculation->getInputPrice(
                $inputPriceType,
                $basePriceForPricingGroup->getPriceWithVat(),
                $newVatPercent
            );
            $productManualInputPrice->setInputPrice($inputPriceForPricingGroup);
        }
    }
}
