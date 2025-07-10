<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataProviderInterface;

class CategorySeoFriendlyUrlDataProvider implements FriendlyUrlDataProviderInterface
{
    protected const string ROUTE_NAME = 'front_category_seo';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData[]
     */
    #[Override]
    public function getFriendlyUrlData(DomainConfig $domainConfig): array
    {
        return [];
    }

    /**
     * @return string
     */
    #[Override]
    public function getRouteName(): string
    {
        return static::ROUTE_NAME;
    }
}
