<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\CurrentDomainRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Symfony\Component\Routing\RouteCollection;

class CurrentDomainRouterTest extends TestCase
{
    public function testDelegateRouter()
    {
        $defaultTimeZone = new DateTimeZone('Europe/Prague');
        $domainConfigs = new DomainConfig(Domain::FIRST_DOMAIN_ID, 'http://example.com:8080', 'example', 'en', $defaultTimeZone);
        $settingMock = $this->createMock(Setting::class);
        $administratorFacadeMock = $this->createMock(AdministratorFacade::class);
        $domain = new Domain([$domainConfigs], $settingMock, $administratorFacadeMock);
        $domain->switchDomainById(Domain::FIRST_DOMAIN_ID);

        $generateResult = 'generateResult';
        $pathInfo = 'pathInfo';
        $matchResult = ['matchResult'];

        $getRouteCollectionResult = new RouteCollection();
        $routerMock = $this->getMockBuilder(DomainRouter::class)
            ->onlyMethods(['__construct', 'generate', 'match', 'getRouteCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $routerMock->expects($this->once())->method('generate')->willReturn($generateResult);
        $routerMock->expects($this->once())->method('match')->with($this->equalTo($pathInfo))->willReturn(
            $matchResult,
        );
        $routerMock->expects($this->once())->method('getRouteCollection')->willReturn($getRouteCollectionResult);

        $domainRouterFactoryMock = $this->getMockBuilder(DomainRouterFactory::class)
            ->onlyMethods(['__construct', 'getRouter'])
            ->disableOriginalConstructor()
            ->getMock();
        $domainRouterFactoryMock->expects($this->exactly(3))->method('getRouter')->willReturn($routerMock);

        $currentDomainRouter = new CurrentDomainRouter($domain, $domainRouterFactoryMock);

        $this->assertSame($generateResult, $currentDomainRouter->generate(''));
        $this->assertSame($matchResult, $currentDomainRouter->match($pathInfo));

        /** @var string $routeCollection */
        $routeCollection = $currentDomainRouter->getRouteCollection();

        $this->assertSame($getRouteCollectionResult, $routeCollection);
    }
}
