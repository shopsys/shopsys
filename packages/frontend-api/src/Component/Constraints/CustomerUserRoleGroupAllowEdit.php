<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CustomerUserRoleGroupAllowEdit extends Constraint
{
    public const CUSTOMER_USER_ROLE_GROUP_CANNOT_BE_CHANGED = '8af342d1-9034-4995-a8cf-60375ca499bf';
    public const LAST_CUSTOMER_USER_ROLE_GROUP_CANNOT_BE_CHANGED = '03ae06a9-2ebd-4376-ad5c-f8e6a7991733';

    public string $message = 'Customer role group cannot be changed.';

    public string $messageForLastCustomerUser = 'Customer role group cannot be changed for last customer user.';

    /**
     * @var array<string, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected static $errorNames = [
        self::CUSTOMER_USER_ROLE_GROUP_CANNOT_BE_CHANGED => 'CUSTOMER_USER_ROLE_GROUP_CANNOT_BE_CHANGED',
        self::LAST_CUSTOMER_USER_ROLE_GROUP_CANNOT_BE_CHANGED => 'LAST_CUSTOMER_USER_ROLE_GROUP_CANNOT_BE_CHANGED',
    ];

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
