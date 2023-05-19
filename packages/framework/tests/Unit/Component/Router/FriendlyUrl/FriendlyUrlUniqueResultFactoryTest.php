<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router\FriendlyUrl;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlUniqueResultFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

class FriendlyUrlUniqueResultFactoryTest extends TestCase
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    private function getDomainConfigs(): array
    {
        return [
            new DomainConfig(Domain::FIRST_DOMAIN_ID, 'http://example.com', 'example.com', 'en'),
        ];
    }

    public function testCreateNewUnique(): void
    {
        $settingMock = $this->createMock(Setting::class);
        $domain = new Domain($this->getDomainConfigs(), $settingMock);

        $friendlyUrlUniqueResultFactory = new FriendlyUrlUniqueResultFactory(
            new FriendlyUrlFactory($domain, new EntityNameResolver([])),
        );

        $attempt = 1;
        $friendlyUrl = new FriendlyUrl('route_name', 7, Domain::FIRST_DOMAIN_ID, 'name');
        $matchedRouteData = null;
        $friendlyUrlUniqueResult = $friendlyUrlUniqueResultFactory->create(
            $attempt,
            $friendlyUrl,
            'name',
            $matchedRouteData,
        );

        $this->assertTrue($friendlyUrlUniqueResult->isUnique());
        $this->assertSame($friendlyUrl, $friendlyUrlUniqueResult->getFriendlyUrlForPersist());
    }

    public function testCreateOldUnique(): void
    {
        $settingMock = $this->createMock(Setting::class);
        $domain = new Domain($this->getDomainConfigs(), $settingMock);

        $friendlyUrlUniqueResultFactory = new FriendlyUrlUniqueResultFactory(
            new FriendlyUrlFactory($domain, new EntityNameResolver([])),
        );

        $attempt = 1;
        $friendlyUrl = new FriendlyUrl('route_name', 7, Domain::FIRST_DOMAIN_ID, 'name');
        $matchedRouteData = [
            '_route' => $friendlyUrl->getRouteName(),
            'id' => $friendlyUrl->getEntityId(),
        ];
        $friendlyUrlUniqueResult = $friendlyUrlUniqueResultFactory->create(
            $attempt,
            $friendlyUrl,
            'name',
            $matchedRouteData,
        );

        $this->assertTrue($friendlyUrlUniqueResult->isUnique());
        $this->assertNull($friendlyUrlUniqueResult->getFriendlyUrlForPersist());
    }

    public function testCreateNotUnique(): void
    {
        $settingMock = $this->createMock(Setting::class);
        $domain = new Domain($this->getDomainConfigs(), $settingMock);

        $friendlyUrlUniqueResultFactory = new FriendlyUrlUniqueResultFactory(
            new FriendlyUrlFactory($domain, new EntityNameResolver([])),
        );

        $attempt = 3;
        $friendlyUrl = new FriendlyUrl('route_name', 7, Domain::FIRST_DOMAIN_ID, 'name');
        $matchedRouteData = [
            '_route' => 'another_route_name',
            'id' => 7,
        ];
        $friendlyUrlUniqueResult = $friendlyUrlUniqueResultFactory->create(
            $attempt,
            $friendlyUrl,
            'name',
            $matchedRouteData,
        );

        $friendlyUrlForPersist = $friendlyUrlUniqueResult->getFriendlyUrlForPersist();
        $this->assertFalse($friendlyUrlUniqueResult->isUnique());
        $this->assertSame($friendlyUrl->getRouteName(), $friendlyUrlForPersist->getRouteName());
        $this->assertSame($friendlyUrl->getEntityId(), $friendlyUrlForPersist->getEntityId());
        $this->assertSame($friendlyUrl->getDomainId(), $friendlyUrlForPersist->getDomainId());
        $this->assertSame('name-4/', $friendlyUrlForPersist->getSlug());
    }
}
