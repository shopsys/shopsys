<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Heureka;

use Shopsys\FrameworkBundle\Component\Setting\Setting;

class HeurekaSetting
{
    public const HEUREKA_API_KEY = 'heurekaApiKey';
    protected const HEUREKA_WIDGET_CODE = 'heurekaWidgetCode';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(protected readonly Setting $setting)
    {
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getApiKeyByDomainId($domainId)
    {
        return $this->setting->getForDomain(static::HEUREKA_API_KEY, $domainId);
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getHeurekaShopCertificationWidgetByDomainId($domainId)
    {
        return $this->setting->getForDomain(static::HEUREKA_WIDGET_CODE, $domainId);
    }

    /**
     * @param string $apiKey
     * @param int $domainId
     */
    public function setApiKeyForDomain($apiKey, $domainId)
    {
        $this->setting->setForDomain(static::HEUREKA_API_KEY, $apiKey, $domainId);
    }

    /**
     * @param string $heurekaWidgetCode
     * @param int $domainId
     */
    public function setHeurekaShopCertificationWidgetForDomain($heurekaWidgetCode, $domainId)
    {
        $this->setting->setForDomain(static::HEUREKA_WIDGET_CODE, $heurekaWidgetCode, $domainId);
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isHeurekaShopCertificationActivated($domainId)
    {
        return $this->setting->getForDomain(static::HEUREKA_API_KEY, $domainId) !== null;
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isHeurekaWidgetActivated($domainId)
    {
        return $this->setting->getForDomain(static::HEUREKA_WIDGET_CODE, $domainId) !== null;
    }
}
