<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

class PaymentInCart extends Constraint
{
    public const UNAVAILABLE_PAYMENT_ERROR = '49287486-fd4a-40e7-b64a-e45fd60b3732';
    public const INVALID_PAYMENT_TRANSPORT_COMBINATION_ERROR = '6b165c10-e644-495a-b5fa-b2bdcadf2670';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::UNAVAILABLE_PAYMENT_ERROR => 'UNAVAILABLE_PAYMENT_ERROR',
        self::INVALID_PAYMENT_TRANSPORT_COMBINATION_ERROR => 'INVALID_PAYMENT_TRANSPORT_COMBINATION_ERROR',
    ];

    /**
     * @param array<string, mixed>|null $options
     * @param string $unavailablePaymentMessage
     * @param string $invalidPaymentTransportCombinationMessage
     * @param array<string>|null $groups
     * @param mixed $payload
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        public string $unavailablePaymentMessage = 'Payment with provided UUID is not available',
        public string $invalidPaymentTransportCombinationMessage = 'The payment is not allowed in combination with already selected transport',
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
