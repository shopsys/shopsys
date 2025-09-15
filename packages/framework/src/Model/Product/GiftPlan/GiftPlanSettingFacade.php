<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

class GiftPlanSettingFacade
{
    public const string GIFT_PRICE_WITH_VAT = 'giftPriceWithVat';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation $basePriceCalculation
     */
    public function __construct(
        protected readonly Setting $setting,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly BasePriceCalculation $basePriceCalculation,
    ) {
    }

    /**
     * @param int $domainId
     * @throws \Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getGiftPriceWithVat(int $domainId): Money
    {
        $value = $this->setting->getForDomain(static::GIFT_PRICE_WITH_VAT, $domainId);

        if ($value === null || $value === '') {
            return Money::zero();
        }

        return Money::create((string)$value);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithVat
     * @param int $domainId
     */
    public function setGiftPriceWithVat(Money $priceWithVat, int $domainId): void
    {
        $this->setting->setForDomain(static::GIFT_PRICE_WITH_VAT, $priceWithVat->getAmount(), $domainId);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @throws \Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException
     * @throws \Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidInputPriceTypeException
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function calculateBaseGiftPrice(int $domainId, Vat $vat): PriceInterface
    {
        $inputPrice = $this->getGiftPriceWithVat($domainId);
        $defaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        return $this->basePriceCalculation->calculateRoundedBasePrice(
            $inputPrice,
            PricingSetting::PRICE_TYPE_WITH_VAT,
            $vat,
            $defaultCurrency,
        );
    }
}
