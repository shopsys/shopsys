<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Attribute;
use Override;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class CustomerUserRoleGroupAllowEdit extends Constraint
{
    public const CUSTOMER_USER_ROLE_GROUP_CANNOT_BE_CHANGED = '8af342d1-9034-4995-a8cf-60375ca499bf';
    public const LAST_CUSTOMER_USER_ROLE_GROUP_CANNOT_BE_CHANGED = '03ae06a9-2ebd-4376-ad5c-f8e6a7991733';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::CUSTOMER_USER_ROLE_GROUP_CANNOT_BE_CHANGED => 'CUSTOMER_USER_ROLE_GROUP_CANNOT_BE_CHANGED',
        self::LAST_CUSTOMER_USER_ROLE_GROUP_CANNOT_BE_CHANGED => 'LAST_CUSTOMER_USER_ROLE_GROUP_CANNOT_BE_CHANGED',
    ];

    /**
     * @param array<string, mixed>|null $options
     * @param string $message
     * @param string $messageForLastCustomerUser
     * @param array<string>|null $groups
     * @param mixed $payload
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        public string $message = 'Customer role group cannot be changed.',
        public string $messageForLastCustomerUser = 'Customer role group cannot be changed for last customer user.',
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
