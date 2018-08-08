<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\CompilerPass;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class FriendlyUrlDataProviderRegistry
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\CompilerPass\FriendlyUrlDataProviderInterface[]
     */
    private $friendlyUrlDataProviders;

    public function __construct()
    {
        $this->friendlyUrlDataProviders = [];
    }

    public function registerFriendlyUrlDataProvider(FriendlyUrlDataProviderInterface $service)
    {
        $this->friendlyUrlDataProviders[] = $service;
    }

    /**
     * @param string $routeName
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData[]
     */
    public function getFriendlyUrlDataByRouteAndDomain($routeName, DomainConfig $domainConfig): array
    {
        foreach ($this->friendlyUrlDataProviders as $friendlyUrlDataProvider) {
            if ($friendlyUrlDataProvider->getRouteName() === $routeName) {
                return $friendlyUrlDataProvider->getFriendlyUrlData($domainConfig);
            }
        }

        throw new \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlRouteNotSupportedException($routeName);
    }
}
