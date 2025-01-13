<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Transaction;

use GoPay\Definition\Response\PaymentStatus;
use Shopsys\ConvertimBundle\Model\Payment\PaymentStatus as ConvertimPaymentStatus;

class ExternalPaymentStatusHelper
{
    protected ?ConvertimPaymentStatus $convertimPaymentStatus = null;

    public function __construct()
    {
        if ($this->isConvertimInstalled()) {
            $this->convertimPaymentStatus = new ConvertimPaymentStatus();
        }
    }

    /**
     * @return bool
     */
    public function isConvertimInstalled(): bool
    {
        return class_exists(ConvertimPaymentStatus::class);
    }

    /**
     * @param string $externalStatus
     * @return string
     */
    public function getTranslatedStatus(string $externalStatus): string
    {
        $statusesToTranslate = array_change_key_case($this->getStatusesToTranslate(), CASE_UPPER);
        $externalStatusUppercase = mb_strtoupper($externalStatus);

        if (array_key_exists($externalStatusUppercase, $statusesToTranslate)) {
            return $statusesToTranslate[$externalStatusUppercase];
        }

        return $externalStatus;
    }

    /**
     * @param string $paymentStatus
     * @return bool
     */
    public function isPaid(string $paymentStatus): bool
    {
        return $paymentStatus === PaymentStatus::PAID ||
            ($this->convertimPaymentStatus !== null && $this->convertimPaymentStatus->isPaid($paymentStatus));
    }

    /**
     * @param string $paymentStatus
     * @return bool
     */
    public function hasPaymentInProcess(string $paymentStatus): bool
    {
        return $paymentStatus === PaymentStatus::PAYMENT_METHOD_CHOSEN ||
            ($this->convertimPaymentStatus !== null && $this->convertimPaymentStatus->hasPaymentInProcess($paymentStatus));
    }

    /**
     * @return array<string, string>
     */
    protected function getStatusesToTranslate(): array
    {
        $statusesToTranslate = [];

        if ($this->convertimPaymentStatus !== null) {
            $statusesToTranslate = $this->convertimPaymentStatus->getStatusesToTranslate();
        }

        return array_merge(
            [
                PaymentStatus::CREATED => t('Payment created'),
                PaymentStatus::PAYMENT_METHOD_CHOSEN => t('Payment method chosen'),
                PaymentStatus::PAID => t('Payment paid'),
                PaymentStatus::AUTHORIZED => t('Payment authorized'),
                PaymentStatus::CANCELED => t('Payment canceled'),
                PaymentStatus::TIMEOUTED => t('Payment has expired'),
                PaymentStatus::REFUNDED => t('Payment refunded'),
                PaymentStatus::PARTIALLY_REFUNDED => t('Payment partially refunded'),
            ],
            $statusesToTranslate,
        );
    }
}
