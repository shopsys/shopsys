<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherRepository;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\OrderCancelledException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\PurchasedGiftVoucherAlreadyRedeemedException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalAlreadyRequestedException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalDeadlinePassedException;

class WithdrawalChecker
{
    public function __construct(
        protected readonly WithdrawalDeadlineCalculation $withdrawalDeadlineCalculation,
        protected readonly WithdrawalRequestRepository $withdrawalRequestRepository,
        protected readonly ClockInterface $clock,
        protected readonly GiftVoucherRepository $giftVoucherRepository,
    ) {
    }

    public function checkOrderWithdrawal(Order $order): void
    {
        if ($order->isCancelled()) {
            throw new OrderCancelledException('Withdrawal is not allowed for cancelled orders');
        }

        if ($this->withdrawalRequestRepository->findByOrder($order) !== null) {
            throw new WithdrawalAlreadyRequestedException('Withdrawal has already been requested for this order');
        }

        if ($this->isWithdrawalBlockedByPurchasedGiftVoucher($order)) {
            throw new PurchasedGiftVoucherAlreadyRedeemedException(
                'Withdrawal is not allowed because a gift voucher purchased in this order has already been redeemed or cancelled',
            );
        }

        if ($this->hasWithdrawalDeadlinePassed($order)) {
            throw new WithdrawalDeadlinePassedException('Withdrawal deadline has passed for this order');
        }
    }

    public function isWithdrawalBlockedByPurchasedGiftVoucher(Order $order): bool
    {
        if (!$order->hasOnlyElectronicGiftVoucherProductItems()) {
            return false;
        }

        if ($this->hasWithdrawalDeadlinePassed($order)) {
            return false;
        }

        foreach ($this->giftVoucherRepository->getAllCreatedOnOrder($order) as $giftVoucher) {
            if (!$giftVoucher->isUnredeemed()) {
                return true;
            }
        }

        return false;
    }

    protected function hasWithdrawalDeadlinePassed(Order $order): bool
    {
        $withdrawalDeadline = $this->withdrawalDeadlineCalculation->getWithdrawalDeadline($order);

        return $withdrawalDeadline !== null && $this->clock->now() > $withdrawalDeadline;
    }
}
