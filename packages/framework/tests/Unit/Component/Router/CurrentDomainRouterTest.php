<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\CurrentDomainRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Tests\FrameworkBundle\Test\DomainConfigHelper;

class CurrentDomainRouterTest extends TestCase
{
    public function testDelegateRouter(): void
    {
        $settingStub = $this->createStub(Setting::class);
        $currentAdministratorStub = $this->createStub(CurrentAdministrator::class);

        $domain = new Domain(
            [DomainConfigHelper::getDomainConfig()],
            $settingStub,
            $currentAdministratorStub,
        );

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

        $contextResolverStub = $this->createStub(ContextResolverInterface::class);

        $currentDomainRouter = new CurrentDomainRouter(
            $domain,
            $domainRouterFactoryMock,
            $contextResolverStub,
        );

        $currentDomainRouter->setContext(new RequestContext());

        $this->assertSame($generateResult, $currentDomainRouter->generate(''));
        $this->assertSame($matchResult, $currentDomainRouter->match($pathInfo));

        $routeCollection = $currentDomainRouter->getRouteCollection();

        $this->assertSame($getRouteCollectionResult, $routeCollection);
    }
}
