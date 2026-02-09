<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;

class GiftPlanSettingFacade
{
    public const string GIFT_INPUT_PRICE = 'giftInputPrice';

    public function __construct(
        protected readonly Setting $setting,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly BasePriceCalculation $basePriceCalculation,
        protected readonly PricingSetting $pricingSetting,
        protected readonly InMemoryCache $inMemoryCache,
        protected readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    public function setInputGiftPrice(Money $inputPrice, int $domainId): void
    {
        $this->setting->setForDomain(static::GIFT_INPUT_PRICE, $inputPrice->getAmount(), $domainId);
    }

    public function getInputGiftPrice(int $domainId): Money
    {
        $value = $this->inMemoryCache->getOrSaveValue(self::GIFT_INPUT_PRICE, fn () => $this->setting->getForDomain(static::GIFT_INPUT_PRICE, $domainId), self::GIFT_INPUT_PRICE);

        if ($value === null || $value === '') {
            return Money::zero();
        }

        return Money::create((string)$value);
    }

    public function calculateBaseGiftPrice(int $domainId, Vat $vat): PriceInterface
    {
        $inputPrice = $this->getInputGiftPrice($domainId);
        $defaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        return $this->basePriceCalculation->calculateRoundedBasePrice(
            $inputPrice,
            $this->pricingSetting->getInputPriceType(),
            $vat,
            $defaultCurrency,
        );
    }

    public function calculateProductGiftPrice(int $domainId, Vat $vat): ProductPrice
    {
        $pricingGroup = $this->currentCustomerUser->getPricingGroup();
        $basePrice = $this->calculateBaseGiftPrice($domainId, $vat);

        return new ProductPrice($basePrice, $pricingGroup, false);
    }
}
