<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Attribute;
use Override;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class CustomerUserRoleGroupChangeAllowed extends Constraint
{
    public const CUSTOMER_USER_ROLE_GROUP_CHANGE_NOT_ALLOWED = 'b8e7bbdd-61c8-408b-bc29-533b68fbc704';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::CUSTOMER_USER_ROLE_GROUP_CHANGE_NOT_ALLOWED => 'CUSTOMER_USER_ROLE_GROUP_CHANGE_NOT_ALLOWED',
    ];

    /**
     * @param array<string>|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        public string $message = 'You cannot change the role group of this customer user to this role group.',
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
        return self::CLASS_CONSTRAINT;
    }
}
