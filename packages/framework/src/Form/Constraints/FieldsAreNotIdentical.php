<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

class FieldsAreNotIdentical extends Constraint
{
    /**
     * @param string $field1
     * @param string $field2
     * @param string $errorPath
     * @param string $message
     * @param array|null $groups
     * @param mixed $payload
     */
    public function __construct(
        public string $field1,
        public string $field2,
        public string $errorPath,
        public string $message = 'Fields must not be identical',
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
