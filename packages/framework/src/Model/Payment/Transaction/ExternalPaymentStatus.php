<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Transaction;

use GoPay\Definition\Response\PaymentStatus;

class ExternalPaymentStatus
{
    /**
     * @param string $externalStatus
     * @return string
     */
    public static function getTranslatedStatus(string $externalStatus): string
    {
        $statusesToTranslate = self::getStatusesToTranslate();
        $externalStatusUppercase = mb_strtoupper($externalStatus);

        if (array_key_exists($externalStatusUppercase, $statusesToTranslate)) {
            return $statusesToTranslate[$externalStatusUppercase];
        }

        return $externalStatus;
    }

    /**
     * @return array<string, string>
     */
    protected static function getStatusesToTranslate(): array
    {
        return [
            PaymentStatus::CREATED => t('Payment created'),
            PaymentStatus::PAYMENT_METHOD_CHOSEN => t('Payment method chosen'),
            PaymentStatus::PAID => t('Payment paid'),
            PaymentStatus::AUTHORIZED => t('Payment authorized'),
            PaymentStatus::CANCELED => t('Payment canceled'),
            PaymentStatus::TIMEOUTED => t('Payment has expired'),
            PaymentStatus::REFUNDED => t('Payment refunded'),
            PaymentStatus::PARTIALLY_REFUNDED => t('Payment partially refunded'),
        ];
    }
}
