<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Router\Exception\NotSupportedException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouter;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use Tests\FrameworkBundle\Test\DomainConfigHelper;

class DomainRouterTest extends TestCase
{
    public function testGetRouter(): void
    {
        $context = new RequestContext();
        $basicRouterStub = $this->createStub(RouterInterface::class);
        $localizedRouterStub = $this->createStub(RouterInterface::class);
        $friendlyUrlRouterStub = $this->createStub(FriendlyUrlRouter::class);
        $transformStringHelper = $this->createStub(TransformStringHelper::class);

        $domainRouter = new DomainRouter(
            $context,
            $basicRouterStub,
            $localizedRouterStub,
            $friendlyUrlRouterStub,
            DomainConfigHelper::getDomainConfig(),
            $transformStringHelper,
        );

        $this->expectException(NotSupportedException::class);
        $domainRouter->setContext($context);
    }
}
