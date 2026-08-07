<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Heureka;

use Shopsys\FrameworkBundle\Component\Setting\Setting;

class HeurekaSetting
{
    public const HEUREKA_API_KEY = 'heurekaApiKey';

    public function __construct(protected readonly Setting $setting)
    {
    }

    public function getApiKeyByDomainId(int $domainId): ?string
    {
        return $this->setting->getForDomain(static::HEUREKA_API_KEY, $domainId);
    }

    public function setApiKeyForDomain(?string $apiKey, int $domainId): void
    {
        $apiKey = trim($apiKey);

        if ($apiKey === '') {
            $apiKey = null;
        }

        $this->setting->setForDomain(static::HEUREKA_API_KEY, $apiKey, $domainId);
    }

    public function isHeurekaShopCertificationActivated(int $domainId): bool
    {
        return $this->setting->getForDomain(static::HEUREKA_API_KEY, $domainId) !== null;
    }
}
