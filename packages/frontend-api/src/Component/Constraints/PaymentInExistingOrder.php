<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Symfony\Component\Validator\Constraint;

class PaymentInExistingOrder extends Constraint
{
    public const UNAVAILABLE_PAYMENT_ERROR = '47fdc56e-1535-43da-a4f1-74e853bf757d';
    public const UNCHANGEABLE_PAYMENT_ERROR = '80144e07-46ed-46a2-8437-7399319856fa';
    public const INVALID_PAYMENT_SWIFT_ERROR = 'c0d72eae-593e-4b8b-946a-41ca67057c39';

    public string $unavailablePaymentMessage = 'Payment {{ paymentUuid }} is not available for order {{ orderUuid }}';

    public string $unchangeablePaymentMessage = 'Payment cannot be changed';

    public string $invalidPaymentSwiftMessage = 'Payment {{ paymentUuid }} cannot be used with SWIFT {{ swift }}';

    /**
     * @var array<string, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected static $errorNames = [
        self::UNAVAILABLE_PAYMENT_ERROR => 'UNAVAILABLE_PAYMENT_ERROR',
        self::UNCHANGEABLE_PAYMENT_ERROR => 'UNCHANGEABLE_PAYMENT_ERROR',
        self::INVALID_PAYMENT_SWIFT_ERROR => 'INVALID_PAYMENT_SWIFT_ERROR',
    ];

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
