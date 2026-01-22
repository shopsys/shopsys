<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

class UniqueCollection extends Constraint
{
    /**
     * @param string $message
     * @param array|null $fields
     * @param bool $allowEmpty
     * @param array|null $groups
     * @param mixed $payload
     */
    public function __construct(
        public string $message = 'Values are duplicate.',
        public ?array $fields = null,
        public bool $allowEmpty = false,
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
