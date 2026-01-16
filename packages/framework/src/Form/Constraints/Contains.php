<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

class Contains extends Constraint
{
    public string $message = 'Field must contain {{ needle }}.';

    public ?string $needle = null;

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getRequiredOptions(): array
    {
        return [
            'needle',
        ];
    }
}
