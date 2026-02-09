<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Withdrawal;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalDeadlinePassedException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalChecker;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalDeadlineCalculation;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestRepository;

class WithdrawalCheckerTest extends TestCase
{
    public function testCanRequestWithdrawalBeforeDeadline(): void
    {
        $deadline = new DateTimeImmutable('2025-01-15 23:59:59');
        $currentDate = new DateTimeImmutable('2025-01-15 23:59:58');

        $checker = $this->createWithdrawalChecker(
            currentDate: $currentDate,
            withdrawalDeadline: $deadline,
        );

        $order = $this->createMock(Order::class);
        $order->method('isCancelled')->willReturn(false);

        $checker->checkOrderWithdrawal($order);

        $this->expectNotToPerformAssertions();
    }

    public function testCannotRequestWithdrawalAfterDeadline(): void
    {
        $deadline = new DateTimeImmutable('2025-01-15 23:59:59');
        $currentDate = new DateTimeImmutable('2025-01-16 00:00:00');

        $checker = $this->createWithdrawalChecker(
            currentDate: $currentDate,
            withdrawalDeadline: $deadline,
        );

        $order = $this->createMock(Order::class);
        $order->method('isCancelled')->willReturn(false);

        $this->expectException(WithdrawalDeadlinePassedException::class);

        $checker->checkOrderWithdrawal($order);
    }

    private function createWithdrawalChecker(
        DateTimeImmutable $currentDate,
        DateTimeImmutable $withdrawalDeadline,
    ): WithdrawalChecker {
        $clock = $this->createMock(ClockInterface::class);
        $clock->method('now')->willReturn($currentDate);

        $withdrawalDeadlineCalculation = $this->createMock(WithdrawalDeadlineCalculation::class);
        $withdrawalDeadlineCalculation->method('getWithdrawalDeadline')->willReturn($withdrawalDeadline);

        $withdrawalRequestRepository = $this->createMock(WithdrawalRequestRepository::class);
        $withdrawalRequestRepository->method('findByOrder')->willReturn(null);

        return new WithdrawalChecker(
            $withdrawalDeadlineCalculation,
            $withdrawalRequestRepository,
            $clock,
        );
    }
}
