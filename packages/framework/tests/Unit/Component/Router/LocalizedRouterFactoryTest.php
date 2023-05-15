<?php

namespace Tests\FrameworkBundle\Unit\Component\Router;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Router\Exception\LocalizedRoutingConfigFileNotFoundException;
use Shopsys\FrameworkBundle\Component\Router\LocalizedRouterFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class LocalizedRouterFactoryTest extends TestCase
{
    protected const LOCALE_ROUTERS_CONFIGURATION_MASK = __DIR__ . '/Resources/routing_front_*.yaml';

    public function testGetRouterRouterNotResolvedException()
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $context = new RequestContext();

        $localizedRouterFactory = new LocalizedRouterFactory(
            static::LOCALE_ROUTERS_CONFIGURATION_MASK,
            $containerMock,
            __DIR__,
        );
        $this->expectException(LocalizedRoutingConfigFileNotFoundException::class);
        $localizedRouterFactory->getRouter('ru', $context);
    }

    public function testGetRouter()
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $context1 = new RequestContext();
        $context1->setHost('host1');
        $context2 = new RequestContext();
        $context2->setHost('host2');

        $localizedRouterFactory = new LocalizedRouterFactory(
            static::LOCALE_ROUTERS_CONFIGURATION_MASK,
            $containerMock,
            __DIR__,
        );

        $router1 = $localizedRouterFactory->getRouter('en', $context1);
        $router2 = $localizedRouterFactory->getRouter('en', $context2);
        $router3 = $localizedRouterFactory->getRouter('en', $context1);
        $router4 = $localizedRouterFactory->getRouter('cs', $context1);

        $this->assertInstanceOf(RouterInterface::class, $router1);
        $this->assertInstanceOf(RouterInterface::class, $router2);
        $this->assertInstanceOf(RouterInterface::class, $router3);
        $this->assertInstanceOf(RouterInterface::class, $router4);

        $this->assertSame($router1, $router3);
        $this->assertNotSame($router1, $router2);
        $this->assertNotSame($router1, $router4);
    }
}
