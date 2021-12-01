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
            'CREATED' => t('Platba založena'),
            'PAYMENT_METHOD_CHOSEN' => t('Platební metoda vybrána'),
            'PAID' => t('Platba zaplacena'),
            'AUTHORIZED' => t('Platba předautorizována'),
            'CANCELED' => t('Platba zrušena'),
            'TIMEOUTED' => t('Vypršelá platnost platby'),
            'REFUNDED' => t('Platba refundována'),
            'PARTIALLY_REFUNDED' => t('Platba částečně refundována'),
        ];

        if (array_key_exists($goPayStatus, $goPayStatusToTranslated)) {
            return $goPayStatusToTranslated[$goPayStatus];
        }

        return $goPayStatus;
    }
}
