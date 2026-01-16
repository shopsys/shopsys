<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

class FileExtensionMaxLength extends Constraint
{
    public string $message = 'File extension {{ value }} is too long. It should have {{ limit }} character or less.';

    public int $limit;

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getRequiredOptions(): array
    {
        return [
            'limit',
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDefaultOption(): ?string
    {
        return 'limit';
    }
}
