<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlRouteNotSupportedException;
use Webmozart\Assert\Assert;

class FriendlyUrlDataProviderRegistry
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataProviderInterface[] $friendlyUrlDataProviders
     */
    public function __construct(protected iterable $friendlyUrlDataProviders)
    {
        Assert::allIsInstanceOf($friendlyUrlDataProviders, FriendlyUrlDataProviderInterface::class);
    }

    /**
     * @param string $routeName
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData[]
     */
    public function getFriendlyUrlDataByRouteAndDomain($routeName, DomainConfig $domainConfig)
    {
        foreach ($this->friendlyUrlDataProviders as $friendlyUrlDataProvider) {
            if ($friendlyUrlDataProvider->getRouteName() === $routeName) {
                return $friendlyUrlDataProvider->getFriendlyUrlData($domainConfig);
            }
        }

        throw new FriendlyUrlRouteNotSupportedException($routeName);
    }
}
