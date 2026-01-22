<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

class PaymentInOrder extends Constraint
{
    public const PAYMENT_NOT_SET_ERROR = 'd4ddd635-44ce-4a73-b343-c306c4dba6c8';
    public const UNAVAILABLE_PAYMENT_ERROR = '84f20917-3db3-454a-8fb5-a1035f46d883';
    public const CHANGED_PAYMENT_PRICE_ERROR = 'd94d726c-1974-4f9b-91e1-8b9da132ff0a';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::PAYMENT_NOT_SET_ERROR => 'PAYMENT_NOT_SET_ERROR',
        self::UNAVAILABLE_PAYMENT_ERROR => 'UNAVAILABLE_PAYMENT_ERROR',
        self::CHANGED_PAYMENT_PRICE_ERROR => 'CHANGED_PAYMENT_PRICE_ERROR',
    ];

    /**
     * @param array<string, mixed>|null $options
     * @param string $paymentNotSetMessage
     * @param string $unavailablePaymentMessage
     * @param string $changedPaymentPriceMessage
     * @param array<string>|null $groups
     * @param mixed $payload
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        public string $paymentNotSetMessage = 'Payment must be set in cart before sending the order',
        public string $unavailablePaymentMessage = 'Payment with provided UUID is not available',
        public string $changedPaymentPriceMessage = 'Selected payment price has changed',
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
