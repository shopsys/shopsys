<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

class UniqueSlugsOnDomains extends Constraint
{
    /**
     * @param string $message
     * @param string $messageDuplicate
     * @param array|null $groups
     * @param mixed $payload
     */
    public function __construct(
        public string $message = 'Address {{ url }} already exists.',
        public string $messageDuplicate = 'Address {{ url }} can be entered only once.',
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
