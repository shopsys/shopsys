<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

class UniqueEmail extends Constraint
{
    /**
     * @param array<string, mixed>|null $options
     * @param string|null $ignoredEmail
     * @param int|null $domainId
     * @param array<string>|null $groups
     * @param mixed $payload
     * @param string $message
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        public ?string $ignoredEmail = null,
        public ?int $domainId = null,
        ?array $groups = null,
        mixed $payload = null,
        public string $message = 'Email {{ email }} is already registered',
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
