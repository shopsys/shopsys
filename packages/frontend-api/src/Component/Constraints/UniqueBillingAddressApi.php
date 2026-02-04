<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Attribute;
use Override;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class UniqueBillingAddressApi extends Constraint
{
    public const DUPLICATE_BILLING_ADDRESS = '9732bc5c-7b8e-404b-ac8c-a1c810f6a045';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::DUPLICATE_BILLING_ADDRESS => 'DUPLICATE_BILLING_ADDRESS',
    ];

    /**
     * @param array<string, mixed>|null $options
     * @param string $message
     * @param array<string>|null $groups
     * @param mixed $payload
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        public string $message = 'Billing address company number {{ company_number }} already exists. Contact customer support.',
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
