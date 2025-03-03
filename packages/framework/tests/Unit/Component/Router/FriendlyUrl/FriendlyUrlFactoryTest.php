<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router\FriendlyUrl;

use DateTimeZone;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlIsNotMultidomainException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;

class FriendlyUrlFactoryTest extends TestCase
{
    public function testCreateForAllDomains()
    {
        $routeName = 'route_name';
        $entityId = 7;
        $namesByLocale = [
            'cs' => 'cs-name',
            'en' => 'en-name',
        ];

        $friendlyUrlFactory = $this->getFriendlyUrlFactory();
        $friendlyUrlFactory->method('isRouteMultidomain')->willReturn(true);

        $friendlyUrls = $friendlyUrlFactory->createForAllDomains($routeName, $entityId, $namesByLocale);

        $this->assertCount(2, $friendlyUrls);

        foreach ($friendlyUrls as $friendlyUrl) {
            $this->assertSame($entityId, $friendlyUrl->getEntityId());
            $this->assertSame($routeName, $friendlyUrl->getRouteName());

            if ($friendlyUrl->getDomainId() === 1) {
                $this->assertSame($namesByLocale['cs'], $friendlyUrl->getSlug());
            } elseif ($friendlyUrl->getDomainId() === 2) {
                $this->assertSame($namesByLocale['en'], $friendlyUrl->getSlug());
            }
        }
    }

    public function testCreateForAllDomainsFailsForSingleDomainRoute()
    {
        $routeName = 'route_name';
        $entityId = 7;
        $namesByLocale = [
            'cs' => 'cs-name',
            'en' => 'en-name',
        ];

        $this->expectException(FriendlyUrlIsNotMultidomainException::class);

        $friendlyUrlFactory = $this->getFriendlyUrlFactory();
        $friendlyUrlFactory->method('isRouteMultidomain')->willReturn(false);

        $friendlyUrlFactory->createForAllDomains($routeName, $entityId, $namesByLocale);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getFriendlyUrlFactory(): FriendlyUrlFactory|MockObject
    {
        $defaultTimeZone = new DateTimeZone('Europe/Prague');
        $domainConfigs = [
            new DomainConfig(Domain::FIRST_DOMAIN_ID, 'http://example.cz', 'example.cz', 'cs', $defaultTimeZone),
            new DomainConfig(Domain::SECOND_DOMAIN_ID, 'http://example.com', 'example.com', 'en', $defaultTimeZone),
        ];
        $settingMock = $this->createMock(Setting::class);
        $administratorFacadeMock = $this->createMock(AdministratorFacade::class);
        $domain = new Domain($domainConfigs, $settingMock, $administratorFacadeMock);
        $domainRouterFactoryMock = $this->createMock(DomainRouterFactory::class);

        $friendlyUrlFactory = $this->getMockBuilder(FriendlyUrlFactory::class)
            ->setConstructorArgs([$domain, new EntityNameResolver([]), new TransformStringHelper(), $domainRouterFactoryMock])
            ->onlyMethods(['isRouteMultidomain'])
            ->getMock();


        return $friendlyUrlFactory;
    }
}
