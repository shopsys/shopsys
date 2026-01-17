<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Service;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Payment\PaymentSetupCreationData;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionData;

interface PaymentServiceInterface
{
    public function createTransaction(
        PaymentTransactionData $paymentTransactionData,
        PaymentSetupCreationData $paymentSetupCreationData,
    ): void;

    public function updateTransaction(PaymentTransactionData $paymentTransactionData): bool;

    public function refundTransaction(PaymentTransactionData $paymentTransactionData, Money $refundAmount): bool;
}
