<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use ArrayAccess;
use Override;
use Symfony\Component\Validator\Constraint;
use Traversable;

class NotInArray extends Constraint
{
    public string $message = 'Value must not be neither of following: {{ array }}';

    public array|Traversable|ArrayAccess $array = [];

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getRequiredOptions(): array
    {
        return [
            'array',
        ];
    }
}
