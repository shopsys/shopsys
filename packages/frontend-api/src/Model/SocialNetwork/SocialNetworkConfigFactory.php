<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\SocialNetwork;

class SocialNetworkConfigFactory
{
    /**
     * @param array $socialNetworkLoginConfig
     */
    public function __construct(
        protected array $socialNetworkLoginConfig,
    ) {
    }

    /**
     * @param int $domainId
     * @param string|null $redirectUrl
     * @return array
     */
    public function createConfigForDomain(int $domainId, ?string $redirectUrl = null): array
    {
        foreach ($this->socialNetworkLoginConfig['providers'] ?? [] as $providerName => $providerSetting) {
            $id = $providerSetting['keys']['id'] ?? '';
            $secret = $providerSetting['keys']['secret'] ?? '';
            $enabledOnDomains = $providerSetting['enabledOnDomains'] ?? [];

            if ($id !== '' && $secret !== '' && in_array($domainId, $enabledOnDomains, true)) {
                $this->socialNetworkLoginConfig['providers'][$providerName]['enabled'] = true;
            } else {
                $this->socialNetworkLoginConfig['providers'][$providerName]['enabled'] = false;
            }
        }

        if ($redirectUrl !== null) {
            $this->socialNetworkLoginConfig['callback'] = $redirectUrl;
        }

        return $this->socialNetworkLoginConfig;
    }
}
