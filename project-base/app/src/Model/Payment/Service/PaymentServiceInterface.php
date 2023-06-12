<?php

declare(strict_types=1);

namespace App\Model\Payment\Service;

use App\FrontendApi\Model\Payment\PaymentSetupCreationData;
use App\Model\Payment\Transaction\PaymentTransactionData;
use Shopsys\FrameworkBundle\Component\Money\Money;

interface PaymentServiceInterface
{
    /**
     * @param \App\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     * @param \App\FrontendApi\Model\Payment\PaymentSetupCreationData $paymentSetupCreationData
     */
    public function createTransaction(
        PaymentTransactionData $paymentTransactionData,
        PaymentSetupCreationData $paymentSetupCreationData,
    ): void;

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
