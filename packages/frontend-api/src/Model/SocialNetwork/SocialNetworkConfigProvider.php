<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\SocialNetwork;

class SocialNetworkConfigProvider
{
    /**
     * @param array $socialNetworkLoginConfig
     */
    public function __construct(
        protected array $socialNetworkLoginConfig,
    ) {
    }

    /**
     * @return array
     */
    public function getSocialNetworkLoginConfig(): array
    {
        return $this->socialNetworkLoginConfig;
    }
}
