<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

class FileAllowedExtension extends Constraint
{
    public string $message = 'File extension {{ value }} is not between allowed extension. Allowed extensions are {{ extensions }}.';

    /**
     * @var string[]
     */
    public array $extensions;

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getRequiredOptions(): array
    {
        return [
            'extensions',
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDefaultOption(): ?string
    {
        return 'extensions';
    }
}
