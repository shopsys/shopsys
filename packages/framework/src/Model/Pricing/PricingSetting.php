<?php

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;

class PricingSetting
{
    const INPUT_PRICE_TYPE = 'inputPriceType';
    const ROUNDING_TYPE = 'roundingType';
    const DEFAULT_CURRENCY = 'defaultCurrencyId';
    const DEFAULT_DOMAIN_CURRENCY = 'defaultDomainCurrencyId';
    const FREE_TRANSPORT_AND_PAYMENT_PRICE_LIMIT = 'freeTransportAndPaymentPriceLimit';

    const INPUT_PRICE_TYPE_WITH_VAT = 1;
    const INPUT_PRICE_TYPE_WITHOUT_VAT = 2;

    const ROUNDING_TYPE_HUNDREDTHS = 1;
    const ROUNDING_TYPE_FIFTIES = 2;
    const ROUNDING_TYPE_INTEGER = 3;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    private $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler
     */
    private $productPriceRecalculationScheduler;

    public function __construct(
        Setting $setting,
        ProductPriceRecalculationScheduler $productPriceRecalculationScheduler
    ) {
        $this->setting = $setting;
        $this->productPriceRecalculationScheduler = $productPriceRecalculationScheduler;
    }

    public function getInputPriceType(): int
    {
        return $this->setting->get(self::INPUT_PRICE_TYPE);
    }

    public function getRoundingType(): int
    {
        return $this->setting->get(self::ROUNDING_TYPE);
    }

    public function getDefaultCurrencyId(): int
    {
        return $this->setting->get(self::DEFAULT_CURRENCY);
    }
    
    public function getDomainDefaultCurrencyIdByDomainId(int $domainId): int
    {
        return $this->setting->getForDomain(self::DEFAULT_DOMAIN_CURRENCY, $domainId);
    }

    public function setDefaultCurrency(Currency $currency): void
    {
        $currency->setExchangeRate(Currency::DEFAULT_EXCHANGE_RATE);
        $this->setting->set(self::DEFAULT_CURRENCY, $currency->getId());
    }
    
    public function setDomainDefaultCurrency(Currency $currency, int $domainId): void
    {
        $this->setting->setForDomain(self::DEFAULT_DOMAIN_CURRENCY, $currency->getId(), $domainId);
    }
    
    public function setRoundingType(int $roundingType): void
    {
        if (!in_array($roundingType, self::getRoundingTypes(), true)) {
            throw new \Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidRoundingTypeException(
                sprintf('Rounding type %s is not valid', $roundingType)
            );
        }

        $this->setting->set(self::ROUNDING_TYPE, $roundingType);
        $this->productPriceRecalculationScheduler->scheduleAllProductsForDelayedRecalculation();
    }
    
    public function getFreeTransportAndPaymentPriceLimit(int $domainId): ?string
    {
        return $this->setting->getForDomain(self::FREE_TRANSPORT_AND_PAYMENT_PRICE_LIMIT, $domainId);
    }

    /**
     * @param string|null $priceLimit
     */
    public function setFreeTransportAndPaymentPriceLimit(int $domainId, ?string $priceLimit = null): void
    {
        $this->setting->setForDomain(self::FREE_TRANSPORT_AND_PAYMENT_PRICE_LIMIT, $priceLimit, $domainId);
    }

    public static function getInputPriceTypes()
    {
        return [
            self::INPUT_PRICE_TYPE_WITHOUT_VAT,
            self::INPUT_PRICE_TYPE_WITH_VAT,
        ];
    }

    public static function getRoundingTypes()
    {
        return [
            self::ROUNDING_TYPE_HUNDREDTHS,
            self::ROUNDING_TYPE_FIFTIES,
            self::ROUNDING_TYPE_INTEGER,
        ];
    }
}
