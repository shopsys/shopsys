<?php

declare(strict_types=1);

namespace App\Model\Payment\Service;

use App\Model\Payment\Transaction\PaymentTransactionData;

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
}
