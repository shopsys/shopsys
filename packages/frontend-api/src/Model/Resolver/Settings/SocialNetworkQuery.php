<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\SocialNetwork\SocialNetworkConfigProvider;

class SocialNetworkQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\SocialNetwork\SocialNetworkConfigProvider $socialNetworkConfigProvider
     */
    public function __construct(
        protected readonly SocialNetworkConfigProvider $socialNetworkConfigProvider,
    ) {
    }

    /**
     * @return string[]
     */
    public function socialNetworkLoginConfigQuery(): array
    {
        $socialNetworkLoginConfig = $this->socialNetworkConfigProvider->getSocialNetworkLoginConfig();
        $enabledProviders = [];

        foreach ($socialNetworkLoginConfig['providers'] ?? [] as $providerName => $providerSetting) {
            if ($providerSetting['enabled'] === true) {
                $enabledProviders[] = $providerName;
            }
        }

        return $enabledProviders;
    }
}
