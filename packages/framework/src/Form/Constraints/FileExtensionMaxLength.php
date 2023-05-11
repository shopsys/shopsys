<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class FileExtensionMaxLength extends Constraint
{
    public string $message = 'File extension {{ value }} is too long. It should have {{ limit }} character or less.';

    public int $limit;

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions(): array
    {
        return [
            'limit',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption(): ?string
    {
        return 'limit';
    }
}
