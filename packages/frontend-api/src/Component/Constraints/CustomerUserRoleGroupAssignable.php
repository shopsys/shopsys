<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Attribute;
use Override;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class CustomerUserRoleGroupAssignable extends Constraint
{
    public const CUSTOMER_USER_ROLE_GROUP_NOT_ASSIGNABLE = 'df19ab00-0513-4f14-80d1-2aa0a34182c7';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::CUSTOMER_USER_ROLE_GROUP_NOT_ASSIGNABLE => 'CUSTOMER_USER_ROLE_GROUP_NOT_ASSIGNABLE',
    ];

    /**
     * @param array<string>|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        public string $message = 'Customer role group cannot be assigned.',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
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
