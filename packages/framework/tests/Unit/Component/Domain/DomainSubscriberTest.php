<?php

namespace Tests\FrameworkBundle\Unit\Component\Domain;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\DomainSubscriber;
use Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class DomainSubscriberTest extends TestCase
{
    public function testOnKernelRequestWithoutMasterRequest(): void
    {
        $event = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
        );

        $settingMock = $this->createMock(Setting::class);

        $domain = new Domain([], $settingMock);

        $domainSubscriber = new DomainSubscriber($domain);
        $domainSubscriber->onKernelRequest($event);
    }

    public function testOnKernelRequestWithMasterRequestAndSetDomain(): void
    {
        $event = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
        );

        $domainMock = $this->getMockBuilder(Domain::class)
            ->onlyMethods(['__construct', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $domainMock->expects($this->once())->method('getId');

        $domainSubscriber = new DomainSubscriber($domainMock);
        $domainSubscriber->onKernelRequest($event);
    }

    public function testOnKernelRequestWithMasterRequest(): void
    {
        $event = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
        );

        $exception = new NoDomainSelectedException();
        $domainMock = $this->getMockBuilder(Domain::class)
            ->onlyMethods(['__construct', 'getId', 'switchDomainByRequest'])
            ->disableOriginalConstructor()
            ->getMock();
        $domainMock->expects($this->once())->method('getId')->willThrowException($exception);
        $domainMock->expects($this->once())->method('switchDomainByRequest')->with($this->equalTo(new Request()));

        $domainSubscriber = new DomainSubscriber($domainMock);
        $domainSubscriber->onKernelRequest($event);
    }
}
