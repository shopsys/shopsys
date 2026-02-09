<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use Attribute;
use Override;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
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
     * @param array<string, mixed>|null $options
     * @param array<string>|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        public string $invalidMessage = 'User with provided email address does not exist.',
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
        return self::PROPERTY_CONSTRAINT;
    }
}
