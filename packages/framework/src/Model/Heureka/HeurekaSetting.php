<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Heureka;

use Shopsys\FrameworkBundle\Component\Setting\Setting;

class HeurekaSetting
{
    public const HEUREKA_API_KEY = 'heurekaApiKey';
    protected const HEUREKA_WIDGET_CODE = 'heurekaWidgetCode';

    public function __construct(protected readonly Setting $setting)
    {
    }

    public function getApiKeyByDomainId(int $domainId): ?string
    {
        return $this->setting->getForDomain(static::HEUREKA_API_KEY, $domainId);
    }

    public function getHeurekaShopCertificationWidgetByDomainId(int $domainId): ?string
    {
        return $this->setting->getForDomain(static::HEUREKA_WIDGET_CODE, $domainId);
    }

    public function setApiKeyForDomain(string $apiKey, int $domainId): void
    {
        $this->setting->setForDomain(static::HEUREKA_API_KEY, $apiKey, $domainId);
    }

    public function setHeurekaShopCertificationWidgetForDomain(string $heurekaWidgetCode, int $domainId): void
    {
        $this->setting->setForDomain(static::HEUREKA_WIDGET_CODE, $heurekaWidgetCode, $domainId);
    }

    public function isHeurekaShopCertificationActivated(int $domainId): bool
    {
        return $this->setting->getForDomain(static::HEUREKA_API_KEY, $domainId) !== null;
    }

    public function isHeurekaWidgetActivated(int $domainId): bool
    {
        return $this->setting->getForDomain(static::HEUREKA_WIDGET_CODE, $domainId) !== null;
    }
}
