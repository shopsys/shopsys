<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Payment;

use GoPay\Definition\Response\PaymentStatus as GoPayPaymentStatus;

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
    public const string ADYEN_STATUS_CANCELED = 'Cancelled';
    public const string ADYEN_STATUS_AUTHORIZED_PENDING = 'AuthorizedPending';
    public const string ADYEN_STATUS_SETTLED_BULK = 'SettledBulk';
    public const string ADYEN_STATUS_REFUNDED_BULK = 'RefundedBulk';

    public const string PAYPAL_STATUS_CREATED = 'CREATED';
    public const string PAYPAL_STATUS_APPROVED = 'APPROVED';
    public const string PAYPAL_STATUS_FAILED = 'FAILED';

    public const string STRIPE_STATUS_SUCCEEDED = 'succeeded';
    public const string STRIPE_STATUS_CANCELED = 'canceled';
    public const string STRIPE_STATUS_PROCESSING = 'processing';
    public const string STRIPE_STATUS_REQUIRES_ACTION = 'requires_action';
    public const string STRIPE_STATUS_REQUIRES_CAPTURE = 'requires_capture';
    public const string STRIPE_STATUS_REQUIRES_CONFIRMATION = 'requires_confirmation';
    public const string STRIPE_STATUS_REQUIRES_PAYMENT_METHOD = 'requires_payment_method';

    /**
     * @param string $paymentStatus
     * @return bool
     */
    public function isPaid(string $paymentStatus): bool
    {
        $paymentStatus = strtoupper($paymentStatus);

        return $paymentStatus === strtoupper(self::ADYEN_STATUS_AUTHORIZED) ||
            $paymentStatus === strtoupper(self::PAYPAL_STATUS_APPROVED) ||
            $paymentStatus === strtoupper(self::STRIPE_STATUS_SUCCEEDED) ||
            $paymentStatus === strtoupper(GoPayPaymentStatus::PAID);
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
            self::STRIPE_STATUS_SUCCEEDED => t('Payment succeeded'),
            self::STRIPE_STATUS_CANCELED => t('Payment canceled'),
            self::STRIPE_STATUS_PROCESSING => t('Payment processing'),
            self::STRIPE_STATUS_REQUIRES_ACTION => t('Payment requires action'),
            self::STRIPE_STATUS_REQUIRES_CAPTURE => t('Payment requires capture'),
            self::STRIPE_STATUS_REQUIRES_CONFIRMATION => t('Payment requires confirmation'),
            self::STRIPE_STATUS_REQUIRES_PAYMENT_METHOD => t('Payment requires payment method'),
            GoPayPaymentStatus::PAYMENT_METHOD_CHOSEN => t('Payment method chosen'),
            GoPayPaymentStatus::PAID => t('Payment paid'),
            GoPayPaymentStatus::AUTHORIZED => t('Payment authorized'),
            GoPayPaymentStatus::CANCELED => t('Payment canceled'),
            GoPayPaymentStatus::TIMEOUTED => t('Payment has expired'),
            GoPayPaymentStatus::REFUNDED => t('Payment refunded'),
            GoPayPaymentStatus::PARTIALLY_REFUNDED => t('Payment partially refunded'),
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
