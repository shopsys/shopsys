<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Router\Exception\LocalizedRoutingConfigFileNotFoundException;
use Shopsys\FrameworkBundle\Component\Router\LocalizedRouterFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RequestContext;

class LocalizedRouterFactoryTest extends TestCase
{
    protected const LOCALE_ROUTERS_CONFIGURATION_MASK = __DIR__ . '/Resources/routing_front_*.yaml';

    public function testGetRouterRouterNotResolvedException(): void
    {
        $containerStub = $this->createStub(ContainerInterface::class);
        $context = new RequestContext();

        $localizedRouterFactory = new LocalizedRouterFactory(
            static::LOCALE_ROUTERS_CONFIGURATION_MASK,
            $containerStub,
            __DIR__,
        );
        $this->expectException(LocalizedRoutingConfigFileNotFoundException::class);
        $localizedRouterFactory->getRouter('ru', $context);
    }

    public function testGetRouter(): void
    {
        $containerStub = $this->createStub(ContainerInterface::class);
        $context1 = new RequestContext();
        $context1->setHost('host1');
        $context2 = new RequestContext();
        $context2->setHost('host2');

        $localizedRouterFactory = new LocalizedRouterFactory(
            static::LOCALE_ROUTERS_CONFIGURATION_MASK,
            $containerStub,
            __DIR__,
        );

        $router1 = $localizedRouterFactory->getRouter('en', $context1);
        $router2 = $localizedRouterFactory->getRouter('en', $context2);
        $router3 = $localizedRouterFactory->getRouter('en', $context1);
        $router4 = $localizedRouterFactory->getRouter('cs', $context1);

        $this->assertSame($router1, $router3);
        $this->assertNotSame($router1, $router2);
        $this->assertNotSame($router1, $router4);
    }
}
