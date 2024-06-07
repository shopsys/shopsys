<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use Symfony\Component\Validator\Constraint;

class ResetPasswordHash extends Constraint
{
    public const INVALID_HASH_ERROR = '82016a50-34c6-4b78-a21d-f9dc5bb47215';

    public string $invalidMessage = 'Provided hash is not valid.';

    /**
     * @var array<string, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected static $errorNames = [
        self::INVALID_HASH_ERROR => 'INVALID_HASH_ERROR',
    ];

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
