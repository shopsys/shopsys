<?php

namespace Shopsys\FrameworkBundle\Model\Heureka;

use Shopsys\FrameworkBundle\Component\Setting\Setting;

class HeurekaSetting
{
    const HEUREKA_API_KEY = 'heurekaApiKey';
    const HEUREKA_WIDGET_CODE = 'heurekaWidgetCode';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    private $setting;

    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
    }

    /**
     * @param int $domainId
     */
    public function getApiKeyByDomainId($domainId): string
    {
        return $this->setting->getForDomain(self::HEUREKA_API_KEY, $domainId);
    }

    /**
     * @param int $domainId
     */
    public function getHeurekaShopCertificationWidgetByDomainId($domainId): string
    {
        return $this->setting->getForDomain(self::HEUREKA_WIDGET_CODE, $domainId);
    }

    /**
     * @param string $apiKey
     * @param int $domainId
     */
    public function setApiKeyForDomain($apiKey, $domainId)
    {
        $this->setting->setForDomain(self::HEUREKA_API_KEY, $apiKey, $domainId);
    }

    /**
     * @param string $heurekaWidgetCode
     * @param int $domainId
     */
    public function setHeurekaShopCertificationWidgetForDomain($heurekaWidgetCode, $domainId)
    {
        $this->setting->setForDomain(self::HEUREKA_WIDGET_CODE, $heurekaWidgetCode, $domainId);
    }

    public function isHeurekaShopCertificationActivated($domainId): bool
    {
        return !empty($this->setting->getForDomain(self::HEUREKA_API_KEY, $domainId));
    }

    public function isHeurekaWidgetActivated($domainId): bool
    {
        return !empty($this->setting->getForDomain(self::HEUREKA_WIDGET_CODE, $domainId));
    }
}
