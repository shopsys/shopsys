<?php

namespace Shopsys\FrameworkBundle\Component\Setting;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Setting\Exception\InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException;

class Setting
{
    public const ORDER_SENT_PAGE_CONTENT = 'orderSubmittedText';
    public const PERSONAL_DATA_DISPLAY_SITE_CONTENT = 'personalDataDisplaySiteContent';
    public const PERSONAL_DATA_EXPORT_SITE_CONTENT = 'personalDataExportSiteContent';
    public const DEFAULT_PRICING_GROUP = 'defaultPricingGroupId';
    public const DEFAULT_AVAILABILITY_IN_STOCK = 'defaultAvailabilityInStockId';
    public const TERMS_AND_CONDITIONS_ARTICLE_ID = 'termsAndConditionsArticleId';
    public const PRIVACY_POLICY_ARTICLE_ID = 'privacyPolicyArticleId';
    public const COOKIES_ARTICLE_ID = 'cookiesArticleId';
    public const DOMAIN_DATA_CREATED = 'domainDataCreated';
    public const FEED_HASH = 'feedHash';
    public const DEFAULT_UNIT = 'defaultUnitId';
    public const BASE_URL = 'baseUrl';
    public const FEED_NAME_TO_CONTINUE = 'feedNameToContinue';
    public const FEED_DOMAIN_ID_TO_CONTINUE = 'feedDomainIdToContinue';
    public const FEED_ITEM_ID_TO_CONTINUE = 'feedItemIdToContinue';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\SettingValue[][]
     */
    protected array $values;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Setting\SettingValueRepository $settingValueRepository
     */
    public function __construct(protected readonly EntityManagerInterface $em, protected readonly SettingValueRepository $settingValueRepository)
    {
        $this->clearCache();
    }

    /**
     * @param string $key
     * @return \DateTime|\Shopsys\FrameworkBundle\Component\Money\Money|string|int|float|bool|null
     */
    public function get($key)
    {
        $this->loadDomainValues(SettingValue::DOMAIN_ID_COMMON);

        if (array_key_exists($key, $this->values[SettingValue::DOMAIN_ID_COMMON])) {
            $settingValue = $this->values[SettingValue::DOMAIN_ID_COMMON][$key];

            return $settingValue->getValue();
        }

        $message = 'Common setting value with name "' . $key . '" not found.';
        throw new SettingValueNotFoundException($message);
    }

    /**
     * @param string $key
     * @param int $domainId
     * @return \DateTime|\Shopsys\FrameworkBundle\Component\Money\Money|string|int|float|bool|null
     */
    public function getForDomain($key, $domainId)
    {
        $this->loadDomainValues($domainId);

        if (array_key_exists($key, $this->values[$domainId])) {
            $settingValue = $this->values[$domainId][$key];

            return $settingValue->getValue();
        }

        $message = 'Setting value with name "' . $key . '" for domain with ID "' . $domainId . '" not found.';
        throw new SettingValueNotFoundException($message);
    }

    /**
     * @param string $key
     * @param \DateTime|\Shopsys\FrameworkBundle\Component\Money\Money|string|int|float|bool|null $value
     */
    public function set($key, $value)
    {
        $this->loadDomainValues(SettingValue::DOMAIN_ID_COMMON);

        if (!array_key_exists($key, $this->values[SettingValue::DOMAIN_ID_COMMON])) {
            $message = 'Common setting value with name "' . $key . '" not found.';
            throw new SettingValueNotFoundException($message);
        }

        $settingValue = $this->values[SettingValue::DOMAIN_ID_COMMON][$key];
        $settingValue->edit($value);

        $this->em->flush();
    }

    /**
     * @param string $key
     * @param \DateTime|\Shopsys\FrameworkBundle\Component\Money\Money|string|int|float|bool|null $value
     * @param int $domainId
     */
    public function setForDomain($key, $value, $domainId)
    {
        $this->loadDomainValues($domainId);

        if (!array_key_exists($key, $this->values[$domainId])) {
            $message = 'Setting value with name "' . $key . '" for domain ID "' . $domainId . '" not found.';
            throw new SettingValueNotFoundException($message);
        }

        $settingValue = $this->values[$domainId][$key];
        $settingValue->edit($value);

        $this->em->flush();
    }

    /**
     * @param int|null $domainId
     */
    protected function loadDomainValues($domainId)
    {
        if ($domainId === null) {
            $message = 'Cannot load setting value for null domain ID';
            throw new InvalidArgumentException($message);
        }

        if (array_key_exists($domainId, $this->values)) {
            return;
        }

        $this->values[$domainId] = [];
        foreach ($this->settingValueRepository->getAllByDomainId($domainId) as $settingValue) {
            $this->values[$domainId][$settingValue->getName()] = $settingValue;
        }
    }

    public function clearCache()
    {
        $this->values = [];
    }
}
