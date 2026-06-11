<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\SocialNetwork;

class SocialNetworkConfigFactory
{
    public function __construct(
        protected array $socialNetworkLoginConfig,
    ) {
    }

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

    public function isFedcmGloballyEnabled(): bool
    {
        return ($this->socialNetworkLoginConfig['fedcm']['enabled'] ?? false) === true;
    }

    public function isFedcmEnabledForDomain(int $domainId, string $providerType): bool
    {
        if (!$this->isFedcmGloballyEnabled()) {
            return false;
        }

        $providerSetting = $this->getProviderSetting($providerType);

        if ($providerSetting === null) {
            return false;
        }

        $isProviderFedcmEnabled = ($providerSetting['fedcm']['enabled'] ?? false) === true;
        $configUrl = $providerSetting['fedcm']['configUrl'] ?? '';
        $clientId = $providerSetting['keys']['id'] ?? '';
        $enabledOnDomains = $providerSetting['enabledOnDomains'] ?? [];

        return $isProviderFedcmEnabled
            && $clientId !== ''
            && $configUrl !== ''
            && in_array($domainId, $enabledOnDomains, true);
    }

    public function getFedcmClientIdForDomain(int $domainId, string $providerType): ?string
    {
        if (!$this->isFedcmEnabledForDomain($domainId, $providerType)) {
            return null;
        }

        return $this->getProviderSetting($providerType)['keys']['id'] ?? null;
    }

    /**
     * @return array<int, array{type: string, clientId: string, configUrl: string, autoSelect: bool, params: array<string, string>}>
     */
    public function getEnabledFedcmProvidersForDomain(int $domainId): array
    {
        if (!$this->isFedcmGloballyEnabled()) {
            return [];
        }

        $enabledProviders = [];

        foreach ($this->socialNetworkLoginConfig['providers'] ?? [] as $providerType => $providerSetting) {
            if (!$this->isFedcmEnabledForDomain($domainId, $providerType)) {
                continue;
            }

            $adapterClass = $providerSetting['adapter'] ?? null;
            $providerDefaultParams = is_string($adapterClass) && is_subclass_of($adapterClass, FedcmAdapterInterface::class)
                ? $adapterClass::getDefaultFedcmParams()
                : [];

            $enabledProviders[] = [
                'type' => $providerType,
                'clientId' => $providerSetting['keys']['id'],
                'configUrl' => $providerSetting['fedcm']['configUrl'],
                'autoSelect' => ($providerSetting['fedcm']['autoSelect'] ?? false) === true,
                'params' => $providerDefaultParams,
            ];
        }

        return $enabledProviders;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function getProviderSetting(string $providerType): ?array
    {
        $providerSetting = $this->socialNetworkLoginConfig['providers'][$providerType] ?? null;

        return is_array($providerSetting) ? $providerSetting : null;
    }
}
