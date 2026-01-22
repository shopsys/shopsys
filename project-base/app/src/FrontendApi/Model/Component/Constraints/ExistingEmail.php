<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use Attribute;
use Override;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ExistingEmail extends Constraint
{
    public const USER_WITH_EMAIL_DOES_NOT_EXIST_ERROR = 'd1bf5f27-fe92-424c-bb58-df90cc7637b1';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::USER_WITH_EMAIL_DOES_NOT_EXIST_ERROR => 'USER_WITH_EMAIL_DOES_NOT_EXIST_ERROR',
    ];

    /**
     * @param string $invalidMessage
     * @param array|null $groups
     * @param mixed $payload
     */
    public function __construct(
        public string $invalidMessage = 'User with provided email address does not exist.',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
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
