<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

class ProductPricesMulticurrencyModeProvider
{
    public function __construct(
        protected readonly string $productPricesMulticurrencyMode,
        protected readonly ProductPricesMulticurrencyModeEnum $productPricesMulticurrencyModeEnum,
    ) {
    }

    public function getMode(): string
    {
        $this->productPricesMulticurrencyModeEnum->validateCase($this->productPricesMulticurrencyMode);

        return $this->productPricesMulticurrencyMode;
    }

    public function isManualMode(): bool
    {
        return $this->getMode() === ProductPricesMulticurrencyModeEnum::MODE_MANUAL;
    }

    public function isCalculatedMode(): bool
    {
        return $this->getMode() === ProductPricesMulticurrencyModeEnum::MODE_CALCULATED;
    }
}
