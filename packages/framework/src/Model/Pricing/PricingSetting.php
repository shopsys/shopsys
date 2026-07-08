<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class PricingSetting
{
    public const string INPUT_PRICE_TYPE = 'inputPriceType';
    public const string SELLING_PRICE_TYPE = 'sellingPriceType';

    public const string DEFAULT_CURRENCY = 'defaultCurrencyId';

    public const int PRICE_TYPE_WITH_VAT = 1;
    public const int PRICE_TYPE_WITHOUT_VAT = 2;

    public function __construct(
        protected readonly Setting $setting,
    ) {
    }

    public function getInputPriceType(): int
    {
        return $this->setting->get(self::INPUT_PRICE_TYPE);
    }

    public function getSellingPriceType(): int
    {
        return $this->setting->get(self::SELLING_PRICE_TYPE);
    }

    public function getDefaultCurrencyId(): int
    {
        return $this->setting->get(self::DEFAULT_CURRENCY);
    }

    public function setDefaultCurrency(Currency $currency): void
    {
        $this->setting->set(self::DEFAULT_CURRENCY, $currency->getId());
    }

    public function getPriceTypes(): array
    {
        return [
            self::PRICE_TYPE_WITHOUT_VAT,
            self::PRICE_TYPE_WITH_VAT,
        ];
    }
}
