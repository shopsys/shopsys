<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderDeliveryDateFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Symfony\Component\Clock\Clock;

class OrderDeliveryDateFacadeTest extends TestCase
{
    #[DataProvider('setDeliveredNowIfNecessaryDataProvider')]
    public function testSetDeliveredNowIfNecessary(
        string $statusType,
        ?DateTime $initialDeliveredAt,
        bool $expectSetDeliveredAtCall,
    ): void {
        $order = $this->createOrderMock(
            $this->createOrderStatus($statusType),
            $initialDeliveredAt,
            $expectSetDeliveredAtCall,
        );

        $orderDeliveryDateFacade = $this->createOrderDeliveryDateFacade();
        $orderDeliveryDateFacade->setDeliveredNowIfNecessary($order);
    }

    /**
     * @return iterable<string, array{statusType: string, initialDeliveredAt: \DateTime|null, expectSetDeliveredAtCall: bool}>
     */
    public static function setDeliveredNowIfNecessaryDataProvider(): iterable
    {
        yield 'delivered at is set when status is done and delivered at is null' => [
            'statusType' => OrderStatusTypeEnum::TYPE_DONE,
            'initialDeliveredAt' => null,
            'expectSetDeliveredAtCall' => true,
        ];

        yield 'delivered at is not set when already set' => [
            'statusType' => OrderStatusTypeEnum::TYPE_DONE,
            'initialDeliveredAt' => new DateTime('2025-01-10 14:00:00'),
            'expectSetDeliveredAtCall' => false,
        ];

        yield 'delivered at is not set when status is not done' => [
            'statusType' => OrderStatusTypeEnum::TYPE_NEW,
            'initialDeliveredAt' => null,
            'expectSetDeliveredAtCall' => false,
        ];
    }

    private function createOrderDeliveryDateFacade(): OrderDeliveryDateFacade
    {
        $emStub = $this->createStub(EntityManagerInterface::class);

        return new OrderDeliveryDateFacade($emStub, Clock::get());
    }

    private function createOrderStatus(string $type): OrderStatus
    {
        $statusData = new OrderStatusData();

        return new OrderStatus($statusData, $type);
    }

    private function createOrderMock(
        OrderStatus $orderStatus,
        ?DateTime $deliveredAt,
        bool $expectSetDeliveredAtCall,
    ): Order|MockObject {
        $orderMock = $this->createMock(Order::class);
        $orderMock->expects($this->any())->method('getStatus')->willReturn($orderStatus);
        $orderMock->expects($this->any())->method('getDeliveredAt')->willReturn($deliveredAt);

        if ($expectSetDeliveredAtCall) {
            $orderMock->expects($this->once())->method('setDeliveredAt');
        } else {
            $orderMock->expects($this->never())->method('setDeliveredAt');
        }

        return $orderMock;
    }
}
