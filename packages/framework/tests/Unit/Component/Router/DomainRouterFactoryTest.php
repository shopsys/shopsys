<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouter;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\LocalizedRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;
use Tests\FrameworkBundle\Test\DomainConfigHelper;

class DomainRouterFactoryTest extends TestCase
{
    public function testGetRouter(): void
    {
        $domainConfig = DomainConfigHelper::getDomainConfig(
            id: Domain::THIRD_DOMAIN_ID,
            locale: 'en',
        );

        $settingStub = $this->createStub(Setting::class);
        $currentAdministratorStub = $this->createStub(CurrentAdministrator::class);

        $domain = new Domain(
            [$domainConfig],
            $settingStub,
            $currentAdministratorStub,
        );

        $localizedRouterStub = $this->createStub(Router::class);
        $friendlyUrlRouterStub = $this->createStub(FriendlyUrlRouter::class);

        $localizedRouterFactoryMock = $this->getMockBuilder(LocalizedRouterFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRouter'])
            ->getMock();
        $localizedRouterFactoryMock
            ->expects($this->once())
            ->method('getRouter')
            ->willReturnCallback(function ($locale, RequestContext $context) use ($localizedRouterStub) {
                $this->assertSame('en', $locale);
                $this->assertSame('example.com', $context->getHost());

                return $localizedRouterStub;
            });

        $friendlyUrlRouterFactoryMock = $this->getMockBuilder(FriendlyUrlRouterFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createRouter'])
            ->getMock();
        $friendlyUrlRouterFactoryMock
            ->expects($this->once())
            ->method('createRouter')
            ->willReturnCallback(
                function (DomainConfig $actualDomainConfig, RequestContext $context) use ($domainConfig, $friendlyUrlRouterStub) {
                    $this->assertSame($domainConfig, $actualDomainConfig);
                    $this->assertSame('example.com', $context->getHost());

                    return $friendlyUrlRouterStub;
                },
            );

        $requestStackStub = $this->createStub(RequestStack::class);
        $containerStub = $this->createStub(ContainerInterface::class);
        $transformStringHelper = $this->createStub(TransformStringHelper::class);

        $domainRouterFactory = new DomainRouterFactory(
            'routerConfiguration',
            $localizedRouterFactoryMock,
            $friendlyUrlRouterFactoryMock,
            $domain,
            $transformStringHelper,
            $requestStackStub,
            $containerStub,
            __DIR__,
        );

        $domainRouterFactory->getRouter(Domain::THIRD_DOMAIN_ID);
    }
}
