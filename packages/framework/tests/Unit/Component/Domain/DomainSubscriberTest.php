<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Domain;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\DomainSubscriber;
use Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class DomainSubscriberTest extends TestCase
{
    public function testOnKernelRequestWithoutMasterRequest(): void
    {
        $event = new RequestEvent(
            $this->createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
        );

        $settingStub = $this->createStub(Setting::class);
        $currentAdministratorStub = $this->createStub(CurrentAdministrator::class);
        $contextResolverStub = $this->createStub(ContextResolverInterface::class);

        $domain = new Domain(
            [],
            $settingStub,
            $currentAdministratorStub,
        );


        $domainSubscriber = new DomainSubscriber($domain, $contextResolverStub);
        $domainSubscriber->onKernelRequest($event);
    }

    public function testOnKernelRequestWithMasterRequestAndSetDomain(): void
    {
        $event = new RequestEvent(
            $this->createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
        );

        $domainMock = $this->getMockBuilder(Domain::class)
            ->onlyMethods(['__construct', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $domainMock->expects($this->once())->method('getId');
        $contextResolverStub = $this->createStub(ContextResolverInterface::class);

        $domainSubscriber = new DomainSubscriber($domainMock, $contextResolverStub);
        $domainSubscriber->onKernelRequest($event);
    }

    public function testOnKernelRequestWithMasterRequest(): void
    {
        $event = new RequestEvent(
            $this->createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
        );

        $exception = new NoDomainSelectedException();
        $domainMock = $this->getMockBuilder(Domain::class)
            ->onlyMethods(['__construct', 'getId', 'switchDomainByRequest'])
            ->disableOriginalConstructor()
            ->getMock();
        $domainMock->expects($this->once())->method('getId')->willThrowException($exception);
        $domainMock->expects($this->once())->method('switchDomainByRequest')->with($this->equalTo(new Request()));

        $contextResolverStub = $this->createStub(ContextResolverInterface::class);

        $domainSubscriber = new DomainSubscriber($domainMock, $contextResolverStub);
        $domainSubscriber->onKernelRequest($event);
    }
}
