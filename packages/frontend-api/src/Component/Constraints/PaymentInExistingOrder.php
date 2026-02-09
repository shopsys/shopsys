<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

class PaymentInExistingOrder extends Constraint
{
    public const UNAVAILABLE_PAYMENT_ERROR = '47fdc56e-1535-43da-a4f1-74e853bf757d';
    public const UNCHANGEABLE_PAYMENT_ERROR = '80144e07-46ed-46a2-8437-7399319856fa';
    public const INVALID_PAYMENT_SWIFT_ERROR = 'c0d72eae-593e-4b8b-946a-41ca67057c39';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::UNAVAILABLE_PAYMENT_ERROR => 'UNAVAILABLE_PAYMENT_ERROR',
        self::UNCHANGEABLE_PAYMENT_ERROR => 'UNCHANGEABLE_PAYMENT_ERROR',
        self::INVALID_PAYMENT_SWIFT_ERROR => 'INVALID_PAYMENT_SWIFT_ERROR',
    ];

    /**
     * @param array<string, mixed>|null $options
     * @param array<string>|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        public string $unavailablePaymentMessage = 'Payment {{ paymentUuid }} is not available for order {{ orderUuid }}',
        public string $unchangeablePaymentMessage = 'Payment cannot be changed',
        public string $invalidPaymentSwiftMessage = 'Payment {{ paymentUuid }} cannot be used with SWIFT {{ swift }}',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        if (is_array($options)) {
            DeprecationHelper::trigger(
                'Passing an array of options to configure the "%s" constraint is deprecated, use named arguments instead.',
                static::class,
            );
        }

        parent::__construct($options, $groups, $payload);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
