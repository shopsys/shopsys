<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Domain;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\DomainSubscriber;
use Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException;
use Shopsys\FrameworkBundle\Component\Router\AdministrationRouter;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
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
        $administratorFacadeMock = $this->createMock(AdministratorFacade::class);
        $administrationFacadeMock = $this->createMock(AdministrationFacade::class);

        $domain = new Domain(
            [],
            $settingMock,
            $administratorFacadeMock,
            $administrationFacadeMock,
        );

        $administrationRouter = $this->createMock(AdministrationRouter::class);

        $domainSubscriber = new DomainSubscriber($domain, $administrationRouter);
        $domainSubscriber->onKernelRequest($event);
    }

    public function testOnKernelRequestWithMasterRequestAndSetDomain(): void
    {
        $event = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
        );

        $domainMock = $this->getMockBuilder(Domain::class)
            ->onlyMethods(['__construct', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $domainMock->expects($this->once())->method('getId');

        $administrationRouter = $this->createMock(AdministrationRouter::class);

        $domainSubscriber = new DomainSubscriber($domainMock, $administrationRouter);
        $domainSubscriber->onKernelRequest($event);
    }

    public function testOnKernelRequestWithMasterRequest(): void
    {
        $event = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
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

        $administrationRouter = $this->createMock(AdministrationRouter::class);

        $domainSubscriber = new DomainSubscriber($domainMock, $administrationRouter);
        $domainSubscriber->onKernelRequest($event);
    }
}
