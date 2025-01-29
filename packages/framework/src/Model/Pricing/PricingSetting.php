<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class PricingSetting
{
    public const string INPUT_PRICE_TYPE = 'inputPriceType';

    public const string DEFAULT_CURRENCY = 'defaultCurrencyId';
    public const string DEFAULT_DOMAIN_CURRENCY = 'defaultDomainCurrencyId';
    public const string FREE_TRANSPORT_AND_PAYMENT_PRICE_LIMIT = 'freeTransportAndPaymentPriceLimit';

    public const int INPUT_PRICE_TYPE_WITH_VAT = 1;
    public const int INPUT_PRICE_TYPE_WITHOUT_VAT = 2;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(
        protected readonly Setting $setting,
    ) {
    }

    /**
     * @return int
     */
    public function getInputPriceType(): int
    {
        return $this->setting->get(self::INPUT_PRICE_TYPE);
    }

    /**
     * @return int
     */
    public function getDefaultCurrencyId(): int
    {
        return $this->setting->get(self::DEFAULT_CURRENCY);
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getDomainDefaultCurrencyIdByDomainId(int $domainId): int
    {
        return $this->setting->getForDomain(self::DEFAULT_DOMAIN_CURRENCY, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     */
    public function setDefaultCurrency(Currency $currency): void
    {
        $this->setting->set(self::DEFAULT_CURRENCY, $currency->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param int $domainId
     */
    public function setDomainDefaultCurrency(Currency $currency, int $domainId): void
    {
        $this->setting->setForDomain(self::DEFAULT_DOMAIN_CURRENCY, $currency->getId(), $domainId);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getFreeTransportAndPaymentPriceLimit(int $domainId): ?Money
    {
        return $this->setting->getForDomain(self::FREE_TRANSPORT_AND_PAYMENT_PRICE_LIMIT, $domainId);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $priceLimit
     */
    public function setFreeTransportAndPaymentPriceLimit(int $domainId, ?Money $priceLimit): void
    {
        $this->setting->setForDomain(self::FREE_TRANSPORT_AND_PAYMENT_PRICE_LIMIT, $priceLimit, $domainId);
    }

    /**
     * @return array
     */
    public function getInputPriceTypes(): array
    {
        return [
            self::INPUT_PRICE_TYPE_WITHOUT_VAT,
            self::INPUT_PRICE_TYPE_WITH_VAT,
        ];
    }
}
