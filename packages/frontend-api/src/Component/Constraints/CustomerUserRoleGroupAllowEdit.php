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

    public string $message = 'Customer role group cannot be changed.';

    /**
     * @var array<string, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected static $errorNames = [
        self::CUSTOMER_USER_ROLE_GROUP_CANNOT_BE_CHANGED => 'CUSTOMER_USER_ROLE_GROUP_CANNOT_BE_CHANGED',
    ];

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
