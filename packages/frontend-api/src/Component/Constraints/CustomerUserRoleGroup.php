<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CustomerUserRoleGroup extends Constraint
{
    public const CUSTOMER_USER_ROLE_GROUP_NOT_FOUND = 'cd01e1cc-a902-497a-94ee-4de24f4d853e';

    public string $message = 'Customer role group not found.';

    /**
     * @var array<string, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected static $errorNames = [
        self::CUSTOMER_USER_ROLE_GROUP_NOT_FOUND => 'CUSTOMER_USER_ROLE_GROUP_NOT_FOUND',
    ];

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
