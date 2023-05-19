<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Contains extends Constraint
{
    public string $message = 'Field must contain {{ needle }}.';

    public ?string $needle = null;

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions(): array
    {
        return [
            'needle',
        ];
    }
}
