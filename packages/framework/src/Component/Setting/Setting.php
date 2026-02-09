<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Setting;

use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException;

class Setting
{
    public const string PERSONAL_DATA_DISPLAY_SITE_CONTENT = 'personalDataDisplaySiteContent';
    public const string PERSONAL_DATA_EXPORT_SITE_CONTENT = 'personalDataExportSiteContent';
    public const string DEFAULT_PRICING_GROUP = 'defaultPricingGroupId';
    public const string TERMS_AND_CONDITIONS_ARTICLE_ID = 'termsAndConditionsArticleId';
    public const string PRIVACY_POLICY_ARTICLE_ID = 'privacyPolicyArticleId';
    public const string USER_CONSENT_POLICY_ARTICLE_ID = 'userConsentPolicyArticleId';
    public const string DOMAIN_DATA_CREATED = 'domainDataCreated';
    public const string FEED_HASH = 'feedHash';
    public const string DEFAULT_UNIT = 'defaultUnitId';
    public const string BASE_URL = 'baseUrl';
    public const string FEED_NAME_TO_CONTINUE = 'feedNameToContinue';
    public const string FEED_DOMAIN_ID_TO_CONTINUE = 'feedDomainIdToContinue';
    public const string FEED_ITEM_ID_TO_CONTINUE = 'feedItemIdToContinue';
    public const string TRANSFER_DAYS_BETWEEN_STOCKS = 'transferDaysBetweenStocks';
    public const string FEED_DELIVERY_DAYS_FOR_OUT_OF_STOCK_PRODUCTS = 'feedDeliveryDaysForOutOfStockProducts';
    public const string CUSTOMER_USER_DEFAULT_GROUP_ROLE_ID = 'customerUserDefaultGroupRoleId';
    public const string FILE_STRUCTURE_MIGRATED_FOR_RELATIONS = 'fileStructureMigratedForRelations';
    public const string CSP_HEADER = 'cspHeader';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\SettingValue[][]
     */
    protected array $values;

    protected bool $allValuesLoaded = false;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly SettingValueRepository $settingValueRepository,
    ) {
        $this->clearCache();
    }

    public function get(
        string $key,
    ): DateTimeInterface|Money|string|int|float|bool|null {
        $this->loadDomainValues(SettingValue::DOMAIN_ID_COMMON);

        if (array_key_exists($key, $this->values[SettingValue::DOMAIN_ID_COMMON])) {
            $settingValue = $this->values[SettingValue::DOMAIN_ID_COMMON][$key];

            return $settingValue->getValue();
        }

        $message = 'Common setting value with name "' . $key . '" not found.';

        throw new SettingValueNotFoundException($message);
    }

    public function getForDomain(string $key, int $domainId): DateTimeInterface|Money|string|int|float|bool|null
    {
        $this->loadDomainValues($domainId);

        if (array_key_exists($key, $this->values[$domainId])) {
            $settingValue = $this->values[$domainId][$key];

            return $settingValue->getValue();
        }

        $message = 'Setting value with name "' . $key . '" for domain with ID "' . $domainId . '" not found.';

        throw new SettingValueNotFoundException($message);
    }

    public function set(string $key, DateTimeInterface|Money|string|int|float|bool|null $value): void
    {
        $this->clearCache();

        $this->loadDomainValues(SettingValue::DOMAIN_ID_COMMON);

        if (!array_key_exists($key, $this->values[SettingValue::DOMAIN_ID_COMMON])) {
            $message = 'Common setting value with name "' . $key . '" not found.';

            throw new SettingValueNotFoundException($message);
        }

        $settingValue = $this->values[SettingValue::DOMAIN_ID_COMMON][$key];
        $settingValue->edit($value);

        $this->em->flush();
    }

    public function setForDomain(
        string $key,
        DateTimeInterface|Money|string|int|float|bool|null $value,
        int $domainId,
    ): void {
        $this->clearCache();

        $this->loadDomainValues($domainId);

        if (!array_key_exists($key, $this->values[$domainId])) {
            $message = 'Setting value with name "' . $key . '" for domain ID "' . $domainId . '" not found.';

            throw new SettingValueNotFoundException($message);
        }

        $settingValue = $this->values[$domainId][$key];
        $settingValue->edit($value);

        $this->em->flush();
    }

    public function initAllDomainsSettings(): void
    {
        if ($this->allValuesLoaded) {
            return;
        }

        $settings = $this->settingValueRepository->getAllDomainsSettingValues();

        foreach ($settings as $settingValue) {
            $domainId = $settingValue->getDomainId();
            $settingName = $settingValue->getName();

            $this->values[$domainId][$settingName] = $settingValue;
        }

        $this->allValuesLoaded = true;
    }

    protected function loadDomainValues(int $domainId): void
    {
        if (array_key_exists($domainId, $this->values)) {
            return;
        }

        $this->values[$domainId] = [];

        foreach ($this->settingValueRepository->getAllByDomainId($domainId) as $settingValue) {
            $this->values[$domainId][$settingValue->getName()] = $settingValue;
        }
    }

    public function deleteByName(string $name): void
    {
        $this->clearCache();

        $this->settingValueRepository->deleteByName($name);
    }

    protected function clearCache(): void
    {
        $this->allValuesLoaded = false;
        $this->values = [];
    }
}
