<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

class FileExtensionMaxLength extends Constraint
{
    /**
     * @param int $limit
     * @param string $message
     * @param array|null $groups
     * @param mixed $payload
     */
    public function __construct(
        public int $limit,
        public string $message = 'File extension {{ value }} is too long. It should have {{ limit }} character or less.',
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
