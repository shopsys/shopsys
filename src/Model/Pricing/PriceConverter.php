<?php

declare(strict_types=1);

namespace App\Model\Pricing;

use App\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter as BasePriceConverter;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;

class PriceConverter extends BasePriceConverter
{
    /**
     * @var \App\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Rounding $rounding
     * @param \App\Component\Setting\Setting $setting
     */
    public function __construct(
        CurrencyFacade $currencyFacade,
        Rounding $rounding,
        Setting $setting
    ) {
        parent::__construct($currencyFacade, $rounding);

        $this->setting = $setting;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param string $vatPercent
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function convertPriceToInputPriceWithoutVatInDomainDefaultCurrency(
        Money $price,
        string $vatPercent,
        int $domainId
    ): Money {
        if ($this->setting->get(PricingSetting::INPUT_PRICE_TYPE) === PricingSetting::INPUT_PRICE_TYPE_WITH_VAT) {
            $multiplier = (string)(100 + (float)$vatPercent);

            $price = $price
                ->multiply($multiplier)
                ->divide(100, 6);
        }

        return $this->convertPriceWithoutVatToPriceInDomainDefaultCurrency($price, $domainId);
    }
}
