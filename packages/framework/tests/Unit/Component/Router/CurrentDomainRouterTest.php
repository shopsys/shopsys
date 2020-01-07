<?php

namespace Tests\FrameworkBundle\Unit\Component\Router;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\CurrentDomainRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class CurrentDomainRouterTest extends TestCase
{
    public function testDelegateRouter()
    {
        $domainConfigs = new DomainConfig(Domain::FIRST_DOMAIN_ID, 'http://example.com:8080', 'example', 'en');
        $settingMock = $this->createMock(Setting::class);
        $domain = new Domain([$domainConfigs], $settingMock);
        $domain->switchDomainById(Domain::FIRST_DOMAIN_ID);

        $generateResult = 'generateResult';
        $pathInfo = 'pathInfo';
        $matchResult = ['matchResult'];
        $getRouteCollectionResult = 'getRouteCollectionResult';
        $routerMock = $this->getMockBuilder(Router::class)
            ->setMethods(['__construct', 'generate', 'match', 'getRouteCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $routerMock->expects($this->once())->method('generate')->willReturn($generateResult);
        $routerMock->expects($this->once())->method('match')->with($this->equalTo($pathInfo))->willReturn($matchResult);
        $routerMock->expects($this->once())->method('getRouteCollection')->willReturn($getRouteCollectionResult);

        $domainRouterFactoryMock = $this->getMockBuilder(DomainRouterFactory::class)
            ->setMethods(['__construct', 'getRouter'])
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
