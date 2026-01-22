<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

class WhitelistPattern extends Constraint
{
    /**
     * @param string $message
     * @param string $blankMessage
     * @param array|null $groups
     * @param mixed $payload
     */
    public function __construct(
        public string $message = 'Invalid whitelist pattern.',
        public string $blankMessage = 'Please enter whitelist pattern.',
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
