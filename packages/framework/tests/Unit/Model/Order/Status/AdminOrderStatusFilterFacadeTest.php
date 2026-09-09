<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Status;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Order\Status\AdminOrderStatusFilterFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class AdminOrderStatusFilterFacadeTest extends TestCase
{
    private const string SESSION_KEY = 'admin_order_status_filter';

    public function testNoOrderStatusIsSelectedByDefault(): void
    {
        $adminOrderStatusFilterFacade = $this->createAdminOrderStatusFilterFacade($session, null);

        $this->assertNull($adminOrderStatusFilterFacade->getSelectedOrderStatusId());
        $this->assertNull($adminOrderStatusFilterFacade->getSelectedOrderStatus());
    }

    public function testSelectedOrderStatusIsReturned(): void
    {
        $orderStatus = $this->createOrderStatusStub(2);
        $adminOrderStatusFilterFacade = $this->createAdminOrderStatusFilterFacade($session, $orderStatus);
        $adminOrderStatusFilterFacade->setSelectedOrderStatusId(2);

        $this->assertSame(2, $adminOrderStatusFilterFacade->getSelectedOrderStatusId());
        $this->assertSame($orderStatus, $adminOrderStatusFilterFacade->getSelectedOrderStatus());
    }

    public function testSelectedOrderStatusIsClearedWhenItNoLongerExists(): void
    {
        $adminOrderStatusFilterFacade = $this->createAdminOrderStatusFilterFacade($session, null);
        $adminOrderStatusFilterFacade->setSelectedOrderStatusId(2);

        $this->assertNull($adminOrderStatusFilterFacade->getSelectedOrderStatusId());
        $this->assertNull($session->get(self::SESSION_KEY));
    }

    public function testSelectedOrderStatusIsClearedByNull(): void
    {
        $adminOrderStatusFilterFacade = $this->createAdminOrderStatusFilterFacade($session, $this->createOrderStatusStub(2));
        $adminOrderStatusFilterFacade->setSelectedOrderStatusId(2);
        $adminOrderStatusFilterFacade->setSelectedOrderStatusId(null);

        $this->assertNull($adminOrderStatusFilterFacade->getSelectedOrderStatusId());
    }

    /**
     * @param-out \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    private function createAdminOrderStatusFilterFacade(
        ?SessionInterface &$session,
        ?OrderStatus $foundOrderStatus,
    ): AdminOrderStatusFilterFacade {
        $session = new Session(new MockArraySessionStorage());
        $request = new Request();
        $request->setSession($session);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $orderStatusRepositoryStub = $this->createStub(OrderStatusRepository::class);
        $orderStatusRepositoryStub->method('findById')->willReturn($foundOrderStatus);

        return new AdminOrderStatusFilterFacade($requestStack, $orderStatusRepositoryStub);
    }

    private function createOrderStatusStub(int $orderStatusId): OrderStatus
    {
        $orderStatusStub = $this->createStub(OrderStatus::class);
        $orderStatusStub->method('getId')->willReturn($orderStatusId);

        return $orderStatusStub;
    }
}
