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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use Tests\FrameworkBundle\Test\DomainConfigHelper;

class DomainRouterFactoryTest extends TestCase
{
    public function testGetRouter(): void
    {
        $domainConfig = DomainConfigHelper::getDomainConfig(
            id: Domain::THIRD_DOMAIN_ID,
            locale: 'en',
        );

        $settingMock = $this->createMock(Setting::class);
        $currentAdministratorMock = $this->createMock(CurrentAdministrator::class);

        $domain = new Domain(
            [$domainConfig],
            $settingMock,
            $currentAdministratorMock,
        );

        $localizedRouterMock = $this->getMockBuilder(RouterInterface::class)->getMock();
        $friendlyUrlRouterMock = $this->getMockBuilder(FriendlyUrlRouter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $localizedRouterFactoryMock = $this->getMockBuilder(LocalizedRouterFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRouter'])
            ->getMock();
        $localizedRouterFactoryMock
            ->expects($this->once())
            ->method('getRouter')
            ->willReturnCallback(function ($locale, RequestContext $context) use ($localizedRouterMock) {
                $this->assertSame('en', $locale);
                $this->assertSame('example.com', $context->getHost());

                return $localizedRouterMock;
            });

        $friendlyUrlRouterFactoryMock = $this->getMockBuilder(FriendlyUrlRouterFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createRouter'])
            ->getMock();
        $friendlyUrlRouterFactoryMock
            ->expects($this->once())
            ->method('createRouter')
            ->willReturnCallback(
                function (DomainConfig $actualDomainConfig, RequestContext $context) use ($domainConfig, $friendlyUrlRouterMock) {
                    $this->assertSame($domainConfig, $actualDomainConfig);
                    $this->assertSame('example.com', $context->getHost());

                    return $friendlyUrlRouterMock;
                },
            );

        $requestStackMock = $this->createMock(RequestStack::class);
        $containerMock = $this->createMock(ContainerInterface::class);
        $transformStringHelper = $this->createMock(TransformStringHelper::class);

        $domainRouterFactory = new DomainRouterFactory(
            'routerConfiguration',
            $localizedRouterFactoryMock,
            $friendlyUrlRouterFactoryMock,
            $domain,
            $transformStringHelper,
            $requestStackMock,
            $containerMock,
            __DIR__,
        );

        $domainRouterFactory->getRouter(Domain::THIRD_DOMAIN_ID);
    }
}
