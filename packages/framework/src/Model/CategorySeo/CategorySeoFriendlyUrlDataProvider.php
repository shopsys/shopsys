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
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData[]
     */
    #[Override]
    public function getFriendlyUrlData(DomainConfig $domainConfig): array
    {
        return [];
    }

    #[Override]
    public function getRouteName(): string
    {
        return static::ROUTE_NAME;
    }
}
