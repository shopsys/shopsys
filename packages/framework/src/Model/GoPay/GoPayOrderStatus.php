<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay;

use GoPay\Definition\Response\PaymentStatus;

class GoPayOrderStatus
{
    /**
     * @param string $goPayStatus
     * @return string
     */
    public static function getTranslatedGoPayStatus(string $goPayStatus): string
    {
        $goPayStatusToTranslate = self::getGoPayStatusesToTranslate();

        if (array_key_exists($goPayStatus, $goPayStatusToTranslate)) {
            return $goPayStatusToTranslate[$goPayStatus];
        }

        return $goPayStatus;
    }

    /**
     * @return array<string, string>
     */
    protected static function getGoPayStatusesToTranslate(): array
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
