<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
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
    public function getRequiredOptions(): array
    {
        return [
            'extensions',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption(): ?string
    {
        return 'extensions';
    }
}
