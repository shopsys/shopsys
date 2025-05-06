<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Router\Exception\NotSupportedException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouter;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class DomainRouterTest extends TestCase
{
    public function testGetRouter()
    {
        $context = new RequestContext();
        $basicRouterMock = $this->getMockBuilder(RouterInterface::class)->getMock();
        $localizedRouterMock = $this->getMockBuilder(RouterInterface::class)->getMock();
        $friendlyUrlRouterMock = $this->getMockBuilder(FriendlyUrlRouter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $transformStringHelper = $this->createMock(TransformStringHelper::class);

        $domainRouter = new DomainRouter(
            $context,
            $basicRouterMock,
            $localizedRouterMock,
            $friendlyUrlRouterMock,
            $this->getDomainConfig(),
            $transformStringHelper,
        );

        $this->expectException(NotSupportedException::class);
        $domainRouter->setContext($context);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    protected function getDomainConfig(): DomainConfig
    {
        $defaultTimeZone = new DateTimeZone('Europe/Prague');

        return new DomainConfig(
            Domain::FIRST_DOMAIN_ID,
            'http://example.com:8080',
            'example.com',
            'cs',
            $defaultTimeZone,
            'http://example.com:8080',
        );
    }
}
