<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay;

use GoPay\Definition\Response\PaymentStatus;
use GoPay\Definition\Response\PaymentSubStatus;

class GoPayOrderStatus
{
    public static function getTranslatedGoPayStatus(string $goPayStatus): string
    {
        $goPayStatusToTranslate = self::getGoPayStatusesToTranslate();

        if (array_key_exists($goPayStatus, $goPayStatusToTranslate)) {
            return $goPayStatusToTranslate[$goPayStatus];
        }

        return $goPayStatus;
    }

    public static function getTranslatedGoPaySubStatus(?string $goPaySubStatus): ?string
    {
        $goPaySubStatusesToTranslate = self::getGoPaySubStatusesToTranslate();

        if ($goPaySubStatus === null) {
            return null;
        }

        return $goPaySubStatusesToTranslate[$goPaySubStatus] ?? null;
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

    /**
     * @return array<string, string>
     */
    protected static function getGoPaySubStatusesToTranslate(): array
    {
        return [
            PaymentSubStatus::_101 => t('Waiting for an online bank transfer to be completed'),
            PaymentSubStatus::_102 => t('Waiting for an offline transfer to be finished'),
            PaymentSubStatus::_3001 => t('Bank payment confirmed by advice'),
            PaymentSubStatus::_3002 => t('Bank payment confirmed by statement'),
            PaymentSubStatus::_3003 => t('Bank transfer not completed'),
            PaymentSubStatus::_5001 => t('Approved with zero amount'),
            PaymentSubStatus::_5002 => t('Declined by issuer - limit reached'),
            PaymentSubStatus::_5003 => t('Declined by issuer - problem at issuer\'s side'),
            PaymentSubStatus::_5004 => t('Declined by issuer - payment card error'),
            PaymentSubStatus::_5005 => t('Declined by issuer - card blocked'),
            PaymentSubStatus::_5006 => t('Declined by issuer - insufficient funds'),
            PaymentSubStatus::_5007 => t('Declined by issuer - card expired'),
            PaymentSubStatus::_5008 => t('Declined by issuer - invalid CVV'),
            PaymentSubStatus::_5009 => t('Declined in the 3DS server'),
            PaymentSubStatus::_5010 => t('Declined by issuer - payment card error'),
            PaymentSubStatus::_5011 => t('Declined by issuer - account error'),
            PaymentSubStatus::_5012 => t('Declined by issuer - technical issues'),
            PaymentSubStatus::_5013 => t('Declined by issuer - wrong card number'),
            PaymentSubStatus::_5014 => t('Declined by issuer - payment card error'),
            PaymentSubStatus::_5015 => t('Declined in the 3DS server'),
            PaymentSubStatus::_5016 => t('Declined by issuer - payment not allowed'),
            PaymentSubStatus::_5017 => t('Declined in the 3DS server'),
            PaymentSubStatus::_5018 => t('Declined in the 3DS server'),
            PaymentSubStatus::_5019 => t('Declined in the 3DS server'),
            PaymentSubStatus::_5020 => t('Unknown configuration'),
            PaymentSubStatus::_5021 => t('Declined by issuer - limit reached'),
            PaymentSubStatus::_5022 => t('Could not reach authorization centre'),
            PaymentSubStatus::_5023 => t('Payment not completed'),
            PaymentSubStatus::_5024 => t('Payment not completed'),
            PaymentSubStatus::_5025 => t('Payment not completed - Reason was shown to customer'),
            PaymentSubStatus::_5026 => t('Payment not completed - Sum of credited amounts exceeded total amount'),
            PaymentSubStatus::_5027 => t('Payment not completed - User not authorized'),
            PaymentSubStatus::_5028 => t('Payment not completed - Customer is not authorized to perform transaction'),
            PaymentSubStatus::_5029 => t('Payment not completed yet'),
            PaymentSubStatus::_5030 => t('Payment not completed - Duplicate payment'),
            PaymentSubStatus::_5031 => t('Technical error on issuer\'s side'),
            PaymentSubStatus::_5033 => t('Unable to deliver SMS'),
            PaymentSubStatus::_5035 => t('Unsupported payment card region'),
            PaymentSubStatus::_5036 => t('Declined by issuer - account error'),
            PaymentSubStatus::_5037 => t('Cardholder canceled the payment'),
            PaymentSubStatus::_5038 => t('Payment not completed'),
            PaymentSubStatus::_5039 => t('Declined by issuer - card blocked'),
            PaymentSubStatus::_5040 => t('Duplicate transaction reversal'),
            PaymentSubStatus::_5041 => t('Duplicate transaction'),
            PaymentSubStatus::_5042 => t('Bank account payment declined'),
            PaymentSubStatus::_5043 => t('Payment canceled by customer'),
            PaymentSubStatus::_5044 => t('SMS was sent but not delivered yet'),
            PaymentSubStatus::_5045 => t('Payment is being processed in the Bitcoin network'),
            PaymentSubStatus::_5046 => t('Payment was not paid in full'),
            PaymentSubStatus::_5047 => t('Payment made after expiration'),
            PaymentSubStatus::_5048 => t('Customer has not given PSD2 consent'),
            PaymentSubStatus::_5049 => t('PSD2 payment declined'),
            PaymentSubStatus::_5050 => t('PSD2 requested account not found'),
            PaymentSubStatus::_6502 => t('Declined in the 3DS server'),
            PaymentSubStatus::_6504 => t('Declined in the 3DS server'),
        ];
    }
}
