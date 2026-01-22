<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

class UniqueEmail extends Constraint
{
    /**
     * @param string $message
     * @param string|null $ignoredEmail
     * @param int|null $domainId
     * @param array|null $groups
     * @param mixed $payload
     */
    public function __construct(
        public string $message = 'Email {{ email }} is already registered',
        public ?string $ignoredEmail = null,
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
