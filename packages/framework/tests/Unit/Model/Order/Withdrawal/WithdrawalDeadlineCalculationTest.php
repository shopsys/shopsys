<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Withdrawal;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\BusinessDayCalculation;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalDeadlineCalculation;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalSetting;

class WithdrawalDeadlineCalculationTest extends TestCase
{
    private const int WITHDRAWAL_DEADLINE_DAYS = 14;
    private const string DISPLAY_TIMEZONE = 'Europe/Prague';

    public function testWithdrawalDeadlineIsNullWhenOrderNotDelivered(): void
    {
        $order = $this->createMock(Order::class);
        $order->method('getDeliveredAt')->willReturn(null);

        $calculation = $this->createWithdrawalDeadlineCalculation();

        $this->assertNull($calculation->getWithdrawalDeadline($order));
    }

    public function testWithdrawalDeadlineIsCalculatedCorrectly(): void
    {
        $deliveredAt = new DateTimeImmutable('2025-01-01 14:30:00', new DateTimeZone(self::DISPLAY_TIMEZONE));

        $order = $this->createMock(Order::class);
        $order->method('getDeliveredAt')->willReturn($deliveredAt);
        $order->method('getDomainId')->willReturn(Domain::FIRST_DOMAIN_ID);

        $withdrawalDeadlineCalculation = $this->createWithdrawalDeadlineCalculation();

        $deadline = $withdrawalDeadlineCalculation->getWithdrawalDeadline($order);

        // Deadline is 2025-01-15 23:59:59 Europe/Prague, converted to UTC = 2025-01-15 22:59:59
        $this->assertEquals('2025-01-15 22:59:59', $deadline->format('Y-m-d H:i:s'));
        $this->assertEquals('UTC', $deadline->getTimezone()->getName());
    }

    public function testWithdrawalDeadlineRespectsTimezoneConversion(): void
    {
        // Delivered at 23:55 UTC, which is 00:55 the next day in Europe/Prague
        $deliveredAt = new DateTimeImmutable('2025-01-01 23:55:00', new DateTimeZone('UTC'));

        $order = $this->createMock(Order::class);
        $order->method('getDeliveredAt')->willReturn($deliveredAt);
        $order->method('getDomainId')->willReturn(Domain::FIRST_DOMAIN_ID);

        $withdrawalDeadlineCalculation = $this->createWithdrawalDeadlineCalculation();

        $deadline = $withdrawalDeadlineCalculation->getWithdrawalDeadline($order);

        // In Europe/Prague, the delivery date is Jan 2nd (00:55), so the deadline is Jan 16th 23:59:59 Europe/Prague
        // Converted to UTC: Jan 16th 22:59:59
        $this->assertEquals('2025-01-16 22:59:59', $deadline->format('Y-m-d H:i:s'));
        $this->assertEquals('UTC', $deadline->getTimezone()->getName());
    }

    private function createWithdrawalDeadlineCalculation(): WithdrawalDeadlineCalculation
    {
        $withdrawalSetting = $this->createMock(WithdrawalSetting::class);
        $withdrawalSetting->method('getDeadlineDays')->willReturn(self::WITHDRAWAL_DEADLINE_DAYS);

        $businessDayCalculation = $this->createMock(BusinessDayCalculation::class);
        $businessDayCalculation->method('getClosestBusinessDay')
            ->willReturnArgument(0);

        $displayTimeZoneProvider = $this->createMock(DisplayTimeZoneProviderInterface::class);
        $displayTimeZoneProvider->method('getDisplayTimeZoneByDomainId')
            ->willReturn(new DateTimeZone(self::DISPLAY_TIMEZONE));

        return new WithdrawalDeadlineCalculation(
            $withdrawalSetting,
            $businessDayCalculation,
            $displayTimeZoneProvider,
        );
    }
}
