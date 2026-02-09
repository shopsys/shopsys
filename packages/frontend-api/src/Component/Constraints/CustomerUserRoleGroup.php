<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Attribute;
use Override;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class CustomerUserRoleGroup extends Constraint
{
    public const CUSTOMER_USER_ROLE_GROUP_NOT_FOUND = 'cd01e1cc-a902-497a-94ee-4de24f4d853e';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::CUSTOMER_USER_ROLE_GROUP_NOT_FOUND => 'CUSTOMER_USER_ROLE_GROUP_NOT_FOUND',
    ];

    /**
     * @param array<string, mixed>|null $options
     * @param array<string>|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        public string $message = 'Customer role group not found.',
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
        return self::PROPERTY_CONSTRAINT;
    }
}
