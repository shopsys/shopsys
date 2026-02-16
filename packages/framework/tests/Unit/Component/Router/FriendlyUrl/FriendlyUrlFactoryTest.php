<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router\FriendlyUrl;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlIsNotMultidomainException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
use Tests\FrameworkBundle\Test\DomainConfigHelper;

class FriendlyUrlFactoryTest extends TestCase
{
    public function testCreateForAllDomains(): void
    {
        $routeName = 'route_name';
        $entityId = 7;
        $namesByLocale = [
            'cs' => 'cs-name',
            'en' => 'en-name',
        ];

        $friendlyUrlFactory = $this->getFriendlyUrlFactory();
        $friendlyUrlFactory->expects($this->atLeastOnce())->method('isRouteMultidomain')->willReturn(true);

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

    public function testCreateForAllDomainsFailsForSingleDomainRoute(): void
    {
        $routeName = 'route_name';
        $entityId = 7;
        $namesByLocale = [
            'cs' => 'cs-name',
            'en' => 'en-name',
        ];

        $this->expectException(FriendlyUrlIsNotMultidomainException::class);

        $friendlyUrlFactory = $this->getFriendlyUrlFactory();
        $friendlyUrlFactory->expects($this->atLeastOnce())->method('isRouteMultidomain')->willReturn(false);

        $friendlyUrlFactory->createForAllDomains($routeName, $entityId, $namesByLocale);
    }

    private function getFriendlyUrlFactory(): FriendlyUrlFactory|MockObject
    {
        $domainConfigs = [
            DomainConfigHelper::getDomainConfig(),
            DomainConfigHelper::getDomainConfig(
                id: Domain::SECOND_DOMAIN_ID,
                locale: 'en',
            ),
        ];
        $settingStub = $this->createStub(Setting::class);
        $currentAdministratorStub = $this->createStub(CurrentAdministrator::class);

        $domain = new Domain(
            $domainConfigs,
            $settingStub,
            $currentAdministratorStub,
        );

        $domainRouterFactoryStub = $this->createStub(DomainRouterFactory::class);

        $friendlyUrlFactory = $this->getMockBuilder(FriendlyUrlFactory::class)
            ->setConstructorArgs([$domain, new EntityNameResolver([]), new TransformStringHelper(), $domainRouterFactoryStub])
            ->onlyMethods(['isRouteMultidomain'])
            ->getMock();


        return $friendlyUrlFactory;
    }
}
