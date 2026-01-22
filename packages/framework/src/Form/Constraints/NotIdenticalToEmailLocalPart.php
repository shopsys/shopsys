<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

class NotIdenticalToEmailLocalPart extends Constraint
{
    /**
     * @param string $password
     * @param string $email
     * @param string $errorPath
     * @param string $message
     * @param array|null $groups
     * @param mixed $payload
     */
    public function __construct(
        public string $password,
        public string $email,
        public string $errorPath,
        public string $message = 'Password cannot be local part of email.',
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
