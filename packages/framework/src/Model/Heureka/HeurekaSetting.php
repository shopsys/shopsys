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
    
    public function getApiKeyByDomainId(int $domainId): string
    {
        return $this->setting->getForDomain(self::HEUREKA_API_KEY, $domainId);
    }
    
    public function getHeurekaShopCertificationWidgetByDomainId(int $domainId): string
    {
        return $this->setting->getForDomain(self::HEUREKA_WIDGET_CODE, $domainId);
    }
    
    public function setApiKeyForDomain(string $apiKey, int $domainId): void
    {
        $this->setting->setForDomain(self::HEUREKA_API_KEY, $apiKey, $domainId);
    }
    
    public function setHeurekaShopCertificationWidgetForDomain(string $heurekaWidgetCode, int $domainId): void
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
