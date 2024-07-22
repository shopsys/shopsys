<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\SocialNetwork;

class SocialNetworkConfigFactory
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\SocialNetwork\SocialNetworkConfigProvider $socialNetworkConfigProvider
     */
    public function __construct(
        protected readonly SocialNetworkConfigProvider $socialNetworkConfigProvider,
    ) {
    }

    /**
     * @param string $redirectUrl
     * @return array
     */
    public function createConfig(string $redirectUrl): array
    {
        return array_merge(['callback' => $redirectUrl], $this->socialNetworkConfigProvider->getSocialNetworkLoginConfig());
    }
}
