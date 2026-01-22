<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use Attribute;
use Override;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS)]
class ResetPasswordHash extends Constraint
{
    public const INVALID_HASH_ERROR = '82016a50-34c6-4b78-a21d-f9dc5bb47215';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::INVALID_HASH_ERROR => 'INVALID_HASH_ERROR',
    ];

    /**
     * @param string $invalidMessage
     * @param array|null $groups
     * @param mixed $payload
     */
    public function __construct(
        public string $invalidMessage = 'Provided hash is not valid.',
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
        return self::CLASS_CONSTRAINT;
    }
}
