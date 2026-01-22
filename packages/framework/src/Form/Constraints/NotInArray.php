<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use ArrayAccess;
use Override;
use Symfony\Component\Validator\Constraint;
use Traversable;

class NotInArray extends Constraint
{
    /**
     * @param array|\Traversable|\ArrayAccess $array
     * @param string $message
     * @param array|null $groups
     * @param mixed $payload
     */
    public function __construct(
        public array|Traversable|ArrayAccess $array,
        public string $message = 'Value must not be neither of following: {{ array }}',
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
