<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
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
     * @param string $message
     * @param int|null $domainId
     * @param array|null $groups
     * @param mixed $payload
     */
    public function __construct(
        public string $message = 'Country with code {{ country_code }} does not exists. Available country codes are {{ available_country_codes }}.',
        public ?int $domainId = null,
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
