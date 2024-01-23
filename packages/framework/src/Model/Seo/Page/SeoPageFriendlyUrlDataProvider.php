<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo\Page;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataProviderInterface;

class SeoPageFriendlyUrlDataProvider implements FriendlyUrlDataProviderInterface
{
    protected const ROUTE_NAME = 'front_page_seo';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData[]
     */
    public function getFriendlyUrlData(DomainConfig $domainConfig): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getRouteName(): string
    {
        return static::ROUTE_NAME;
    }
}
