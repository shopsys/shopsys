<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router\FriendlyUrl;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

class FriendlyUrlFactoryTest extends TestCase
{
    public function testCreateForAllDomains()
    {
        $defaultTimeZone = new DateTimeZone('Europe/Prague');
        $domainConfigs = [
            new DomainConfig(Domain::FIRST_DOMAIN_ID, 'http://example.cz', 'example.cz', 'cs', $defaultTimeZone),
            new DomainConfig(Domain::SECOND_DOMAIN_ID, 'http://example.com', 'example.com', 'en', $defaultTimeZone),
        ];
        $settingMock = $this->createMock(Setting::class);
        $domain = new Domain($domainConfigs, $settingMock);

        $friendlyUrlFactory = new FriendlyUrlFactory($domain, new EntityNameResolver([]));

        $routeName = 'route_name';
        $entityId = 7;
        $namesByLocale = [
            'cs' => 'cs-name',
            'en' => 'en-name',
        ];

        $friendlyUrls = $friendlyUrlFactory->createForAllDomains($routeName, $entityId, $namesByLocale);
        $this->assertCount(2, $friendlyUrls);

        foreach ($friendlyUrls as $friendlyUrl) {
            $this->assertSame($entityId, $friendlyUrl->getEntityId());
            $this->assertSame($routeName, $friendlyUrl->getRouteName());

            if ($friendlyUrl->getDomainId() === 1) {
                $this->assertSame($namesByLocale['cs'] . '/', $friendlyUrl->getSlug());
            } elseif ($friendlyUrl->getDomainId() === 2) {
                $this->assertSame($namesByLocale['en'] . '/', $friendlyUrl->getSlug());
            }
        }
    }
}
