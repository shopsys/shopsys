<?php

namespace Tests\ShopBundle\Unit\Component\Router;

use PHPUnit_Framework_TestCase;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouter;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class DomainRouterTest extends PHPUnit_Framework_TestCase
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
        $this->expectException(\Shopsys\FrameworkBundle\Component\Router\Exception\NotSupportedException::class);
        $domainRouter->setContext($context);
    }
}
