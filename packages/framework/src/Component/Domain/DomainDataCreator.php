<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use Shopsys\FrameworkBundle\Component\Domain\Multidomain\MultidomainEntityDataCreator;
use Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Setting\SettingValueRepository;
use Shopsys\FrameworkBundle\Component\Translation\TranslatableEntityDataCreator;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatSetting;

class DomainDataCreator
{
    public const TEMPLATE_DOMAIN_ID = 1;

    public function __construct(
        protected readonly Domain $domain,
        protected readonly Setting $setting,
        protected readonly SettingValueRepository $settingValueRepository,
        protected readonly MultidomainEntityDataCreator $multidomainEntityDataCreator,
        protected readonly TranslatableEntityDataCreator $translatableEntityDataCreator,
        protected readonly PricingGroupDataFactory $pricingGroupDataFactory,
        protected readonly PricingGroupFacade $pricingGroupFacade,
        protected readonly VatDataFactory $vatDataFactory,
        protected readonly VatFacade $vatFacade,
        protected readonly VatSetting $vatSetting,
        protected readonly AdditionalServiceFacade $additionalServiceFacade,
    ) {
    }

    public function createNewDomainsData(): int
    {
        $newDomainsCount = 0;

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domainConfig) {
            $domainId = $domainConfig->getId();

            try {
                $this->setting->getForDomain(Setting::DOMAIN_DATA_CREATED, $domainId);
            } catch (SettingValueNotFoundException $ex) {
                $locale = $domainConfig->getLocale();
                $isNewLocale = $this->isNewLocale($locale);
                $this->settingValueRepository->copyAllMultidomainSettings(self::TEMPLATE_DOMAIN_ID, $domainId);
                $this->setting->setForDomain(Setting::BASE_URL, $domainConfig->getUrl(), $domainId);

                $this->processDefaultPricingGroupForNewDomain($domainId);
                $this->processDefaultVatForNewDomain($domainId);

                $this->multidomainEntityDataCreator->copyAllMultidomainDataForNewDomain(
                    self::TEMPLATE_DOMAIN_ID,
                    $domainId,
                );

                $this->additionalServiceFacade->useProductVatRateWhereVatIsMissing($domainId);

                if ($isNewLocale) {
                    $this->translatableEntityDataCreator->copyAllTranslatableDataForNewLocale(
                        $this->getTemplateLocale(),
                        $locale,
                    );
                }
                $newDomainsCount++;
            }
        }

        return $newDomainsCount;
    }

    protected function isNewLocale(string $locale): bool
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            if ($domainConfig->getLocale() === $locale) {
                return false;
            }
        }

        return true;
    }

    protected function getTemplateLocale(): string
    {
        return $this->domain->getDomainConfigById(self::TEMPLATE_DOMAIN_ID)->getLocale();
    }

    protected function processDefaultPricingGroupForNewDomain(int $domainId): void
    {
        $pricingGroup = $this->createDefaultPricingGroupForNewDomain($domainId);
        $this->setting->setForDomain(Setting::DEFAULT_PRICING_GROUP, $pricingGroup->getId(), $domainId);
    }

    protected function createDefaultPricingGroupForNewDomain(
        int $domainId,
    ): PricingGroup {
        $domain = $this->domain->getDomainConfigById($domainId);
        $pricingGroupData = $this->pricingGroupDataFactory->create();
        $pricingGroupData->name = t('Default pricing group', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $domain->getLocale());

        return $this->pricingGroupFacade->create($pricingGroupData, $domainId);
    }

    protected function processDefaultVatForNewDomain(int $domainId): void
    {
        $vat = $this->createDefaultVatForNewDomain($domainId);
        $this->vatSetting->setDefaultVatId($vat->getId(), $domainId);
    }

    protected function createDefaultVatForNewDomain(int $domainId): Vat
    {
        $domain = $this->domain->getDomainConfigById($domainId);
        $vatData = $this->vatDataFactory->create();
        $vatData->name = t('Default VAT rate', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $domain->getLocale());
        $vatData->percent = '0';

        return $this->vatFacade->create($vatData, $domainId);
    }
}
