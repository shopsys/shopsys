<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay;

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
            'CREATED' => t('Payment created'),
            'PAYMENT_METHOD_CHOSEN' => t('Payment method chosen'),
            'PAID' => t('Payment paid'),
            'AUTHORIZED' => t('Payment authorized'),
            'CANCELED' => t('Payment canceled'),
            'TIMEOUTED' => t('Payment has expired'),
            'REFUNDED' => t('Payment refunded'),
            'PARTIALLY_REFUNDED' => t('Payment partially refunded'),
        ];
    }
}
