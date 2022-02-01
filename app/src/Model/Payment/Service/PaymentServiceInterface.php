<?php

declare(strict_types=1);

namespace App\Model\Payment\Service;

use App\Model\Payment\Transaction\PaymentTransactionData;
use Shopsys\FrameworkBundle\Component\Money\Money;

interface PaymentServiceInterface
{
    /**
     * @param \App\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     * @return array
     */
    public function createTransaction(PaymentTransactionData $paymentTransactionData): array;

    /**
     * @param \App\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     * @return bool
     */
    public function updateTransaction(PaymentTransactionData $paymentTransactionData): bool;

    /**
     * @param \App\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $refundAmount
     * @return bool
     */
    public function refundTransaction(PaymentTransactionData $paymentTransactionData, Money $refundAmount): bool;
}
