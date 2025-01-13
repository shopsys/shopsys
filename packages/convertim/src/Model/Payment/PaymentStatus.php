<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Payment;

class PaymentStatus
{
    public const string ADYEN_STATUS_AUTHORIZED = 'Authorised';
    public const string ADYEN_STATUS_RECEIVED = 'Received';
    public const string ADYEN_STATUS_SENT_FOR_SETTLE = 'SentForSettle';
    public const string ADYEN_STATUS_SETTLE_SCHEDULED = 'SettleScheduled';
    public const string ADYEN_STATUS_SETTLED = 'Settled';
    public const string ADYEN_STATUS_SENT_FOR_REFUND = 'SentForRefund';
    public const string ADYEN_STATUS_REFUND_SCHEDULED = 'RefundScheduled';
    public const string ADYEN_STATUS_REFUNDED = 'Refunded';
    public const string ADYEN_STATUS_REFUSED = 'Refused';
    public const string ADYEN_STATUS_ERROR = 'Error';
    public const string ADYEN_STATUS_EXPIRED = 'Expired';
    public const string ADYEN_STATUS_CANCELED = 'Canceled';
    public const string ADYEN_STATUS_AUTHORIZED_PENDING = 'AuthorizedPending';
    public const string ADYEN_STATUS_SETTLED_BULK = 'SettledBulk';
    public const string ADYEN_STATUS_REFUNDED_BULK = 'RefundedBulk';

    public const string PAYPAL_STATUS_CREATED = 'CREATED';
    public const string PAYPAL_STATUS_APPROVED = 'APPROVED';
    public const string PAYPAL_STATUS_FAILED = 'FAILED';

    /**
     * @param string $paymentStatus
     * @return bool
     */
    public function isPaid(string $paymentStatus): bool
    {
        $paymentStatus = strtoupper($paymentStatus);

        return $paymentStatus === strtoupper(self::ADYEN_STATUS_AUTHORIZED) || $paymentStatus === strtoupper(self::PAYPAL_STATUS_APPROVED);
    }

    /**
     * @return array
     */
    public function getStatusesToTranslate(): array
    {
        return [
            self::ADYEN_STATUS_AUTHORIZED => t('Payment authorized'),
            self::ADYEN_STATUS_RECEIVED => t('Payment received'),
            self::ADYEN_STATUS_SENT_FOR_SETTLE => t('Payment sent for settle'),
            self::ADYEN_STATUS_SETTLE_SCHEDULED => t('Payment settle scheduled'),
            self::ADYEN_STATUS_SETTLED => t('Payment settled'),
            self::ADYEN_STATUS_SENT_FOR_REFUND => t('Payment sent for refund'),
            self::ADYEN_STATUS_REFUND_SCHEDULED => t('Payment refund scheduled'),
            self::ADYEN_STATUS_REFUNDED => t('Payment refunded'),
            self::ADYEN_STATUS_REFUSED => t('Payment refused'),
            self::ADYEN_STATUS_ERROR => t('Payment error'),
            self::ADYEN_STATUS_EXPIRED => t('Payment expired'),
            self::ADYEN_STATUS_CANCELED => t('Payment canceled'),
            self::ADYEN_STATUS_AUTHORIZED_PENDING => t('Payment authorized pending'),
            self::ADYEN_STATUS_SETTLED_BULK => t('Payment settled bulk'),
            self::ADYEN_STATUS_REFUNDED_BULK => t('Payment refunded bulk'),
            self::PAYPAL_STATUS_CREATED => t('Payment created'),
            self::PAYPAL_STATUS_APPROVED => t('Payment approved'),
            self::PAYPAL_STATUS_FAILED => t('Payment failed'),
        ];
    }

    /**
     * @param string $paymentStatus
     * @return bool
     */
    public function hasPaymentInProcess(string $paymentStatus): bool
    {
        return false;
    }
}
