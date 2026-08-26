<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

class TransportInOrder extends Constraint
{
    public const TRANSPORT_NOT_SET_ERROR = '2a993918-fb80-4aba-a94c-dcb165dc2817';
    public const TRANSPORT_UNAVAILABLE_ERROR = '74fe6c4d-928a-4459-b7c1-01c34043de69';
    public const MISSING_PICKUP_PLACE_IDENTIFIER_ERROR = '72cfdb60-9779-4903-a845-57e14b730795';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::TRANSPORT_NOT_SET_ERROR => 'TRANSPORT_NOT_SET_ERROR',
        self::TRANSPORT_UNAVAILABLE_ERROR => 'TRANSPORT_UNAVAILABLE_ERROR',
        self::MISSING_PICKUP_PLACE_IDENTIFIER_ERROR => 'MISSING_PICKUP_PLACE_IDENTIFIER_ERROR',
    ];

    /**
     * @param array<string, mixed>|null $options
     * @param array<string>|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        public string $transportNotSetMessage = 'Transport must be set in cart before sending the order',
        public string $transportUnavailableMessage = 'Selected transport is not available',
        public string $missingPickupPlaceIdentifierMessage = 'Selected transport needs to have pickup place identifier set',
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
