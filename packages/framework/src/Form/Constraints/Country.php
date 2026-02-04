<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

class Country extends Constraint
{
    public const string INVALID_COUNTRY_ERROR = '9080a4de-347f-48c7-a41a-b4cc46a5146d';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::INVALID_COUNTRY_ERROR => 'INVALID_COUNTRY_ERROR',
    ];

    /**
     * @param array<string, mixed>|null $options
     * @param string $message
     * @param int|null $domainId
     * @param array<string>|null $groups
     * @param mixed $payload
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        public string $message = 'Country with code {{ country_code }} does not exists. Available country codes are {{ available_country_codes }}.',
        public ?int $domainId = null,
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
