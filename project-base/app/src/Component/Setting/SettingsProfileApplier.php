<?php

declare(strict_types=1);

namespace App\Component\Setting;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;

class SettingsProfileApplier
{
    public function __construct(
        private readonly Setting $setting,
        private readonly CurrencyFacade $currencyFacade,
        private readonly Localization $localization,
    ) {
    }

    public function applyProfile(string $profileName, int $domainId = Domain::FIRST_DOMAIN_ID): void
    {
        switch ($profileName) {
            case 'alt-pricing':
                $this->applyAltPricingProfile($domainId);
                break;
            case 'baseline':
            default:
                $this->applyBaselineProfile($domainId);
                break;
        }
    }

    private function applyAltPricingProfile(int $domainId): void
    {
        // Alt-pricing profile: CZK, CS locale, input with VAT
        $this->setCurrency($domainId, 'CZK');
        $this->setLocale($domainId, 'cs');
        $this->setPriceInputType($domainId, PricingSetting::INPUT_PRICE_TYPE_WITH_VAT);
    }

    private function applyBaselineProfile(int $domainId): void
    {
        // Baseline profile: EUR, EN locale, input without VAT (current defaults)
        $this->setCurrency($domainId, 'EUR');
        $this->setLocale($domainId, 'en');
        $this->setPriceInputType($domainId, PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT);
    }

    private function setCurrency(int $domainId, string $currencyCode): void
    {
        $currency = $this->currencyFacade->getByCode($currencyCode);
        $this->setting->setForDomain(PricingSetting::DEFAULT_CURRENCY, $currency->getId(), $domainId);
    }

    private function setLocale(int $domainId, string $localeCode): void
    {
        $this->localization->setAdminLocaleForDomain($localeCode, $domainId);
    }

    private function setPriceInputType(int $domainId, int $inputPriceType): void
    {
        $this->setting->setForDomain(PricingSetting::INPUT_PRICE_TYPE, $inputPriceType, $domainId);
    }

    public function getVatRate(string $vatType, int $domainId): string
    {
        // Pro různé profily můžeme v budoucnu vracet různé sazby
        // Zatím vracíme standardní sazby
        return match ($vatType) {
            'VAT_HIGH' => '21',
            'VAT_LOW' => '15',
            'VAT_SECOND_LOW' => '10',
            'VAT_ZERO' => '0',
            default => '21',
        };
    }
}