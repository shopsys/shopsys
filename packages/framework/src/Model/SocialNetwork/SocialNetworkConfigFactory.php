<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\SocialNetwork;

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
     * @param string $redirectUrl
     * @return array
     */
    public function createConfig(string $redirectUrl): array
    {
        return array_merge(['callback' => $redirectUrl], $this->socialNetworkLoginConfig);
    }
}
