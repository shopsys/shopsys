<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Withdrawal;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherRepository;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\PurchasedGiftVoucherAlreadyRedeemedException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalDeadlinePassedException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalChecker;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalDeadlineCalculation;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestRepository;

class WithdrawalCheckerTest extends TestCase
{
    private const string DEADLINE = '2025-01-15 23:59:59';

    private const string BEFORE_DEADLINE = '2025-01-15 23:59:58';

    private const string AFTER_DEADLINE = '2025-01-16 00:00:00';

    public function testCanRequestWithdrawalBeforeDeadline(): void
    {
        $checker = $this->createWithdrawalChecker(
            currentDate: new DateTimeImmutable(self::BEFORE_DEADLINE),
            withdrawalDeadline: new DateTimeImmutable(self::DEADLINE),
        );

        $checker->checkOrderWithdrawal($this->createOrderStub(false));

        $this->expectNotToPerformAssertions();
    }

    public function testCannotRequestWithdrawalAfterDeadline(): void
    {
        $checker = $this->createWithdrawalChecker(
            currentDate: new DateTimeImmutable(self::AFTER_DEADLINE),
            withdrawalDeadline: new DateTimeImmutable(self::DEADLINE),
        );

        $this->expectException(WithdrawalDeadlinePassedException::class);

        $checker->checkOrderWithdrawal($this->createOrderStub(false));
    }

    public function testWithdrawalBlockedWhenOnlyRedeemedElectronicVoucherWithinDeadline(): void
    {
        $checker = $this->createWithdrawalChecker(
            currentDate: new DateTimeImmutable(self::BEFORE_DEADLINE),
            withdrawalDeadline: new DateTimeImmutable(self::DEADLINE),
            giftVouchers: [$this->createGiftVoucherStub(false)],
        );

        $this->expectException(PurchasedGiftVoucherAlreadyRedeemedException::class);

        $checker->checkOrderWithdrawal($this->createOrderStub(true));
    }

    public function testWithdrawalAllowedWhenOrderContainsOtherGoodsBesidesRedeemedVoucher(): void
    {
        $checker = $this->createWithdrawalChecker(
            currentDate: new DateTimeImmutable(self::BEFORE_DEADLINE),
            withdrawalDeadline: new DateTimeImmutable(self::DEADLINE),
            giftVouchers: [$this->createGiftVoucherStub(false)],
        );

        $checker->checkOrderWithdrawal($this->createOrderStub(false));

        $this->expectNotToPerformAssertions();
    }

    public function testWithdrawalAllowedWhenPurchasedVoucherIsStillUnredeemed(): void
    {
        $checker = $this->createWithdrawalChecker(
            currentDate: new DateTimeImmutable(self::BEFORE_DEADLINE),
            withdrawalDeadline: new DateTimeImmutable(self::DEADLINE),
            giftVouchers: [$this->createGiftVoucherStub(true)],
        );

        $checker->checkOrderWithdrawal($this->createOrderStub(true));

        $this->expectNotToPerformAssertions();
    }

    public function testDeadlineMessageTakesPrecedenceOverRedeemedVoucherAfterDeadline(): void
    {
        $checker = $this->createWithdrawalChecker(
            currentDate: new DateTimeImmutable(self::AFTER_DEADLINE),
            withdrawalDeadline: new DateTimeImmutable(self::DEADLINE),
            giftVouchers: [$this->createGiftVoucherStub(false)],
        );

        $this->expectException(WithdrawalDeadlinePassedException::class);

        $checker->checkOrderWithdrawal($this->createOrderStub(true));
    }

    public function testWithdrawalIsNotBlockedByPurchasedGiftVoucherAfterDeadline(): void
    {
        $checker = $this->createWithdrawalChecker(
            currentDate: new DateTimeImmutable(self::AFTER_DEADLINE),
            withdrawalDeadline: new DateTimeImmutable(self::DEADLINE),
            giftVouchers: [$this->createGiftVoucherStub(false)],
        );

        $this->assertFalse($checker->isWithdrawalBlockedByPurchasedGiftVoucher($this->createOrderStub(true)));
    }

    public function testWithdrawalIsBlockedByPurchasedGiftVoucherWithinDeadline(): void
    {
        $checker = $this->createWithdrawalChecker(
            currentDate: new DateTimeImmutable(self::BEFORE_DEADLINE),
            withdrawalDeadline: new DateTimeImmutable(self::DEADLINE),
            giftVouchers: [$this->createGiftVoucherStub(false)],
        );

        $this->assertTrue($checker->isWithdrawalBlockedByPurchasedGiftVoucher($this->createOrderStub(true)));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher[] $giftVouchers
     */
    private function createWithdrawalChecker(
        DateTimeImmutable $currentDate,
        DateTimeImmutable $withdrawalDeadline,
        array $giftVouchers = [],
    ): WithdrawalChecker {
        $clock = $this->createStub(ClockInterface::class);
        $clock->method('now')->willReturn($currentDate);

        $withdrawalDeadlineCalculation = $this->createStub(WithdrawalDeadlineCalculation::class);
        $withdrawalDeadlineCalculation->method('getWithdrawalDeadline')->willReturn($withdrawalDeadline);

        $withdrawalRequestRepository = $this->createStub(WithdrawalRequestRepository::class);
        $withdrawalRequestRepository->method('findByOrder')->willReturn(null);

        $giftVoucherRepository = $this->createStub(GiftVoucherRepository::class);
        $giftVoucherRepository->method('getAllCreatedOnOrder')->willReturn($giftVouchers);

        return new WithdrawalChecker(
            $withdrawalDeadlineCalculation,
            $withdrawalRequestRepository,
            $clock,
            $giftVoucherRepository,
        );
    }

    private function createOrderStub(bool $hasOnlyElectronicGiftVoucherProductItems): Order
    {
        $order = $this->createStub(Order::class);
        $order->method('isCancelled')->willReturn(false);
        $order->method('hasOnlyElectronicGiftVoucherProductItems')
            ->willReturn($hasOnlyElectronicGiftVoucherProductItems);

        return $order;
    }

    private function createGiftVoucherStub(bool $isUnredeemed): GiftVoucher
    {
        $giftVoucher = $this->createStub(GiftVoucher::class);
        $giftVoucher->method('isUnredeemed')->willReturn($isUnredeemed);

        return $giftVoucher;
    }
}
