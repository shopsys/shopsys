<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Router\Exception\NotSupportedException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouter;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class DomainRouterTest extends TestCase
{
    public function testGetRouter()
    {
        $context = new RequestContext();
        $basicRouterMock = $this->getMockBuilder(RouterInterface::class)->getMockForAbstractClass();
        $localizedRouterMock = $this->getMockBuilder(RouterInterface::class)->getMockForAbstractClass();
        $friendlyUrlRouterMock = $this->getMockBuilder(FriendlyUrlRouter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $domainRouter = new DomainRouter($context, $basicRouterMock, $localizedRouterMock, $friendlyUrlRouterMock);
        $this->expectException(NotSupportedException::class);
        $domainRouter->setContext($context);
    }
}
