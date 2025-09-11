<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

class GiftPlanSettingFacade
{
    public const string GIFT_PRICE_WITH_VAT = 'giftPriceWithVat';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(
        protected readonly Setting $setting,
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
}
