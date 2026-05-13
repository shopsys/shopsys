<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Administrator\Activity;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityRequestSubscriber;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\AdministratorIsNotLoggedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AdministratorActivityRequestSubscriberTest extends TestCase
{
    public function testUpdateAdministratorActivityUpdatesLoggedAdministratorOnMainAdminRequest(): void
    {
        $administratorStub = $this->createStub(Administrator::class);

        $administratorActivityFacadeMock = $this->createMock(AdministratorActivityFacade::class);
        $administratorActivityFacadeMock->expects($this->once())->method('updateCurrentActivity')->with($administratorStub);

        $subscriber = $this->createSubscriber(
            true,
            $administratorStub,
            $administratorActivityFacadeMock,
        );

        $subscriber->updateAdministratorActivity($this->createRequestEvent(HttpKernelInterface::MAIN_REQUEST));
    }

    public function testUpdateAdministratorActivityDoesNothingOnSubRequest(): void
    {
        $contextResolverMock = $this->createMock(ContextResolverInterface::class);
        $contextResolverMock->expects($this->never())->method('isCurrentContext');

        $currentAdministratorMock = $this->createMock(CurrentAdministrator::class);
        $currentAdministratorMock->expects($this->never())->method('getCurrentlyLoggedAdministrator');

        $administratorActivityFacadeMock = $this->createMock(AdministratorActivityFacade::class);
        $administratorActivityFacadeMock->expects($this->never())->method('updateCurrentActivity');

        $subscriber = new AdministratorActivityRequestSubscriber(
            $contextResolverMock,
            $currentAdministratorMock,
            $administratorActivityFacadeMock,
        );

        $subscriber->updateAdministratorActivity($this->createRequestEvent(HttpKernelInterface::SUB_REQUEST));
    }

    public function testUpdateAdministratorActivityDoesNothingOutsideAdminContext(): void
    {
        $currentAdministratorMock = $this->createMock(CurrentAdministrator::class);
        $currentAdministratorMock->expects($this->never())->method('getCurrentlyLoggedAdministrator');

        $administratorActivityFacadeMock = $this->createMock(AdministratorActivityFacade::class);
        $administratorActivityFacadeMock->expects($this->never())->method('updateCurrentActivity');

        $subscriber = $this->createSubscriber(
            false,
            null,
            $administratorActivityFacadeMock,
            $currentAdministratorMock,
        );

        $subscriber->updateAdministratorActivity($this->createRequestEvent(HttpKernelInterface::MAIN_REQUEST));
    }

    public function testUpdateAdministratorActivityDoesNothingWhenAdministratorIsNotLogged(): void
    {
        $currentAdministratorMock = $this->createMock(CurrentAdministrator::class);
        $currentAdministratorMock
            ->expects($this->once())
            ->method('getCurrentlyLoggedAdministrator')
            ->willThrowException(new AdministratorIsNotLoggedException('Administrator is not logged.'));

        $administratorActivityFacadeMock = $this->createMock(AdministratorActivityFacade::class);
        $administratorActivityFacadeMock->expects($this->never())->method('updateCurrentActivity');

        $subscriber = $this->createSubscriber(
            true,
            null,
            $administratorActivityFacadeMock,
            $currentAdministratorMock,
        );

        $subscriber->updateAdministratorActivity($this->createRequestEvent(HttpKernelInterface::MAIN_REQUEST));
    }

    private function createSubscriber(
        bool $isAdminContext,
        ?Administrator $administrator,
        AdministratorActivityFacade $administratorActivityFacade,
        ?CurrentAdministrator $currentAdministrator = null,
    ): AdministratorActivityRequestSubscriber {
        $contextResolverMock = $this->createMock(ContextResolverInterface::class);
        $contextResolverMock
            ->expects($this->once())
            ->method('isCurrentContext')
            ->with(AdminContext::class)
            ->willReturn($isAdminContext);

        if ($currentAdministrator === null) {
            $currentAdministrator = $this->createStub(CurrentAdministrator::class);

            if ($administrator !== null) {
                $currentAdministrator->method('getCurrentlyLoggedAdministrator')->willReturn($administrator);
            }
        }

        return new AdministratorActivityRequestSubscriber(
            $contextResolverMock,
            $currentAdministrator,
            $administratorActivityFacade,
        );
    }

    private function createRequestEvent(int $requestType): RequestEvent
    {
        return new RequestEvent(
            $this->createStub(HttpKernelInterface::class),
            new Request(),
            $requestType,
        );
    }
}
