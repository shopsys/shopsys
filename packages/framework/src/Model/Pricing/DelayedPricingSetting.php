<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidInputPriceTypeException;

class DelayedPricingSetting
{
    public function __construct(
        protected readonly PricingSetting $pricingSetting,
        protected readonly InputPriceRecalculationScheduler $inputPriceRecalculationScheduler,
    ) {
    }

    public function scheduleSetInputPriceType(int $inputPriceType): void
    {
        if (!in_array($inputPriceType, $this->pricingSetting->getPriceTypes(), true)) {
            throw new InvalidInputPriceTypeException('Unknown input price type');
        }

        $currentInputPriceType = $this->pricingSetting->getInputPriceType();

        if ($currentInputPriceType === $inputPriceType) {
            return;
        }

        switch ($inputPriceType) {
            case PricingSetting::PRICE_TYPE_WITHOUT_VAT:
                $this->inputPriceRecalculationScheduler->scheduleSetInputPricesWithoutVat();

                break;

            case PricingSetting::PRICE_TYPE_WITH_VAT:
                $this->inputPriceRecalculationScheduler->scheduleSetInputPricesWithVat();

                break;
        }
    }
}
