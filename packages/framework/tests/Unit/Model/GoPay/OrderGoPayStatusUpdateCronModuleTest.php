<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\GoPay;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use RuntimeException;
use Shopsys\FrameworkBundle\Model\GoPay\Exception\GoPayPaymentDownloadException;
use Shopsys\FrameworkBundle\Model\GoPay\GoPayFacade;
use Shopsys\FrameworkBundle\Model\GoPay\OrderGoPayStatusUpdateCronModule;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceFacade;
use Symfony\Component\Clock\DatePoint;

class OrderGoPayStatusUpdateCronModuleTest extends TestCase
{
    public function testRunThrowsWhenAnyOrderFailsAndStillProcessesSuccessfulOrder(): void
    {
        $failedOrder = $this->createOrderStub(1000);
        $successfulOrderStatus = $this->createStub(OrderStatus::class);
        $successfulOrder = $this->createOrderStub(1001, [false, true], $successfulOrderStatus);

        $emMock = $this->createMock(EntityManagerInterface::class);
        $emMock->expects($this->once())->method('flush');

        $goPayFacadeStub = $this->createStub(GoPayFacade::class);
        $goPayFacadeStub->method('getAllUnpaidGoPayOrders')
            ->willReturn([$failedOrder, $successfulOrder]);

        $paymentServiceFacadeMock = $this->createMock(PaymentServiceFacade::class);
        $paymentServiceFacadeMock->expects($this->exactly(2))
            ->method('updatePaymentTransactionsByOrder')
            ->willReturnCallback(function (Order $order) use ($failedOrder, $successfulOrder) {
                if ($order === $failedOrder) {
                    throw new GoPayPaymentDownloadException(
                        '/payments/payment-id',
                        'GET',
                        200,
                        null,
                        null,
                    );
                }

                return $order === $successfulOrder;
            });

        $orderFacadeMock = $this->createMock(OrderFacade::class);
        $orderFacadeMock->expects($this->once())
            ->method('updatePaymentByLastPaymentTransaction')
            ->with($successfulOrder);

        $orderMailFacadeMock = $this->createMock(OrderMailFacade::class);
        $orderMailFacadeMock->expects($this->once())
            ->method('sendEmail')
            ->with($successfulOrder, $successfulOrderStatus);

        $clockStub = $this->createStub(ClockInterface::class);
        $clockStub->method('now')->willReturn(new DatePoint('2026-01-01 00:00:00'));

        $orderGoPayStatusUpdateCronModule = new OrderGoPayStatusUpdateCronModule(
            $emMock,
            $goPayFacadeStub,
            $orderMailFacadeMock,
            $paymentServiceFacadeMock,
            $orderFacadeMock,
            $clockStub,
        );
        $orderGoPayStatusUpdateCronModule->setLogger($this->createStub(Logger::class));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('GoPay status update failed for 1 order(s).');

        $orderGoPayStatusUpdateCronModule->run();
    }

    /**
     * @param array<int,bool> $isPaidReturns
     */
    private function createOrderStub(
        int $orderId,
        array $isPaidReturns = [false],
        ?OrderStatus $orderStatus = null,
    ): Order {
        $orderStub = $this->getStubBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getId',
                'isDeleted',
                'getGoPayTransactionStatusesIndexedByGoPayId',
                'isPaid',
                'getGoPayTransactions',
                'getStatus',
            ])
            ->getStub();

        $orderStub->method('getId')->willReturn($orderId);
        $orderStub->method('isDeleted')->willReturn(false);
        $orderStub->method('getGoPayTransactionStatusesIndexedByGoPayId')->willReturn([]);

        if (count($isPaidReturns) === 1) {
            $orderStub->method('isPaid')->willReturn($isPaidReturns[0]);
        } else {
            $orderStub->method('isPaid')->willReturnOnConsecutiveCalls(...$isPaidReturns);
        }
        $orderStub->method('getGoPayTransactions')->willReturn([]);
        $orderStub->method('getStatus')->willReturn($orderStatus ?? $this->createStub(OrderStatus::class));

        return $orderStub;
    }
}
