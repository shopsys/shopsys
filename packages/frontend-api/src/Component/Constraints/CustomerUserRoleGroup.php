<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
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
     */
    protected const array ERROR_NAMES = [
        self::CUSTOMER_USER_ROLE_GROUP_NOT_FOUND => 'CUSTOMER_USER_ROLE_GROUP_NOT_FOUND',
    ];

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
