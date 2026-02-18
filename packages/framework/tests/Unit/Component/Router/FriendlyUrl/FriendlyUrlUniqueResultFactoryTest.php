<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router\FriendlyUrl;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlUniqueResultFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
use Tests\FrameworkBundle\Test\DomainConfigHelper;

class FriendlyUrlUniqueResultFactoryTest extends TestCase
{
    public function testCreateNewUnique(): void
    {
        $settingStub = $this->createStub(Setting::class);
        $currentAdministratorStub = $this->createStub(CurrentAdministrator::class);

        $domain = new Domain(
            [DomainConfigHelper::getDomainConfig()],
            $settingStub,
            $currentAdministratorStub,
        );

        $domainRouterFactoryStub = $this->createStub(DomainRouterFactory::class);

        $friendlyUrlUniqueResultFactory = new FriendlyUrlUniqueResultFactory(
            new FriendlyUrlFactory($domain, new EntityNameResolver([]), new TransformStringHelper(), $domainRouterFactoryStub),
            $domain,
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
        $settingStub = $this->createStub(Setting::class);
        $currentAdministratorStub = $this->createStub(CurrentAdministrator::class);

        $domain = new Domain(
            [DomainConfigHelper::getDomainConfig()],
            $settingStub,
            $currentAdministratorStub,
        );

        $domainRouterFactoryStub = $this->createStub(DomainRouterFactory::class);

        $friendlyUrlUniqueResultFactory = new FriendlyUrlUniqueResultFactory(
            new FriendlyUrlFactory($domain, new EntityNameResolver([]), new TransformStringHelper(), $domainRouterFactoryStub),
            $domain,
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
        $settingStub = $this->createStub(Setting::class);
        $currentAdministratorStub = $this->createStub(CurrentAdministrator::class);

        $domain = new Domain(
            [DomainConfigHelper::getDomainConfig()],
            $settingStub,
            $currentAdministratorStub,
        );

        $domainRouterFactoryStub = $this->createStub(DomainRouterFactory::class);

        $friendlyUrlUniqueResultFactory = new FriendlyUrlUniqueResultFactory(
            new FriendlyUrlFactory($domain, new EntityNameResolver([]), new TransformStringHelper(), $domainRouterFactoryStub),
            $domain,
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
        $this->assertSame('name-4', $friendlyUrlForPersist->getSlug());
    }

    public function testCreateNotUniqueWhenSlugConflictsWithAnotherDomainPostfix(): void
    {
        $settingStub = $this->createStub(Setting::class);
        $currentAdministratorStub = $this->createStub(CurrentAdministrator::class);

        $domain = new Domain(
            [
                DomainConfigHelper::getDomainConfig(),
                DomainConfigHelper::getDomainConfig(
                    id: 2,
                    url: DomainConfigHelper::DEFAULT_EXAMPLE_COM_BASE_URL . '/sk',
                    name: 'example.com SK',
                    locale: 'sk',
                    postfix: '/sk',
                ),
            ],
            $settingStub,
            $currentAdministratorStub,
        );

        $domainRouterFactoryStub = $this->createStub(DomainRouterFactory::class);

        $friendlyUrlUniqueResultFactory = new FriendlyUrlUniqueResultFactory(
            new FriendlyUrlFactory($domain, new EntityNameResolver([]), new TransformStringHelper(), $domainRouterFactoryStub),
            $domain,
        );

        $attempt = 1;
        $friendlyUrl = new FriendlyUrl('route_name', 7, Domain::FIRST_DOMAIN_ID, 'sk');
        $matchedRouteData = null;
        $friendlyUrlUniqueResult = $friendlyUrlUniqueResultFactory->create(
            $attempt,
            $friendlyUrl,
            'sk',
            $matchedRouteData,
        );

        $friendlyUrlForPersist = $friendlyUrlUniqueResult->getFriendlyUrlForPersist();
        $this->assertFalse($friendlyUrlUniqueResult->isUnique());
        $this->assertSame($friendlyUrl->getRouteName(), $friendlyUrlForPersist->getRouteName());
        $this->assertSame($friendlyUrl->getEntityId(), $friendlyUrlForPersist->getEntityId());
        $this->assertSame($friendlyUrl->getDomainId(), $friendlyUrlForPersist->getDomainId());
        $this->assertSame('sk-2', $friendlyUrlForPersist->getSlug());
    }
}
