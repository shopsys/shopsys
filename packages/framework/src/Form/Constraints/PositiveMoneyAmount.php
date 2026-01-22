<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

class PositiveMoneyAmount extends Constraint
{
    /**
     * @param string $message
     * @param array|null $groups
     * @param mixed $payload
     */
    public function __construct(
        public string $message = 'Amount of money should be greater to zero.',
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
