<?php

declare(strict_types=1);

namespace App\Model\GoPay;

class GoPayOrderStatus
{
    /**
     * @param string $goPayStatus
     * @return string
     */
    public static function getTranslatedGoPayStatus(string $goPayStatus): string
    {
        $goPayStatusToTranslated = [
            'CREATED' => t('Payment created'),
            'PAYMENT_METHOD_CHOSEN' => t('Payment method chosen'),
            'PAID' => t('Payment paid'),
            'AUTHORIZED' => t('Payment authorized'),
            'CANCELED' => t('Payment canceled'),
            'TIMEOUTED' => t('Payment has expired'),
            'REFUNDED' => t('Payment refunded'),
            'PARTIALLY_REFUNDED' => t('Payment partially refunded'),
        ];

        if (array_key_exists($goPayStatus, $goPayStatusToTranslated)) {
            return $goPayStatusToTranslated[$goPayStatus];
        }

        return $goPayStatus;
    }
}
