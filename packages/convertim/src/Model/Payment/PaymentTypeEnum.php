<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Payment;

use Shopsys\FrameworkBundle\Model\Payment\AbstractPaymentTypeEnum;

class PaymentTypeEnum extends AbstractPaymentTypeEnum
{
    public const string TYPE_CASH_ON_DELIVERY = 'convertim_cash_on_delivery';
    public const string TYPE_QR = 'convertim_qr';
    public const string TYPE_ADYEN = 'convertim_adyen';
    public const string TYPE_STRIPE = 'convertim_stripe';
    public const string TYPE_COMGATE = 'convertim_comgate';
    public const string TYPE_TRUSTPAY = 'convertim_trustpay';
    public const string TYPE_PAYPAL = 'convertim_paypal';
    public const string TYPE_ESSOX = 'convertim_essox';

    /**
     * @return array<string, string>
     */
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('Cash on delivery (Convertim only)') => self::TYPE_CASH_ON_DELIVERY,
            t('QR (Convertim only)') => self::TYPE_QR,
            t('Adyen (Convertim only)') => self::TYPE_ADYEN,
            t('Stripe (Convertim only)') => self::TYPE_STRIPE,
            t('Comgate (Convertim only)') => self::TYPE_COMGATE,
            t('TrustPay (Convertim only)') => self::TYPE_TRUSTPAY,
            t('PayPal (Convertim only)') => self::TYPE_PAYPAL,
            t('Essox (Convertim only)') => self::TYPE_ESSOX,
        ];
    }

    /**
     * @return bool
     */
    public function shouldBeDisplayedInDefaultEshopCart(): bool
    {
        return false;
    }
}
