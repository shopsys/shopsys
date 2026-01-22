<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

class UniqueEntityField extends Constraint
{
    /**
     * @param string $fieldName
     * @param string $entityName
     * @param string $message
     * @param object|null $entityInstance
     * @param array|null $groups
     * @param mixed $payload
     */
    public function __construct(
        public string $fieldName,
        public string $entityName,
        public string $message = 'The "{{ value }}" value of "{{ fieldName }}" field must be unique',
        public ?object $entityInstance = null,
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
        return self::PROPERTY_CONSTRAINT;
    }
}
