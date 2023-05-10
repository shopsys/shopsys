<?php

declare(strict_types=1);

namespace App\Model\SeoPage;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataProviderInterface;

class SeoPageFriendlyUrlDataProvider implements FriendlyUrlDataProviderInterface
{
    private const ROUTE_NAME = 'front_page_seo';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Component\Router\FriendlyUrl\FriendlyUrlData[]
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
        return self::ROUTE_NAME;
    }
}
