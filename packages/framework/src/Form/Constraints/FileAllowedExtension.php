<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

class FileAllowedExtension extends Constraint
{
    /**
     * @param array<string> $extensions
     * @param string $message
     * @param array|null $groups
     * @param mixed $payload
     */
    public function __construct(
        public array $extensions,
        public string $message = 'File extension {{ value }} is not between allowed extension. Allowed extensions are {{ extensions }}.',
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
