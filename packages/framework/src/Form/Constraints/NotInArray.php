<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use ArrayAccess;
use Symfony\Component\Validator\Constraint;
use Traversable;

/**
 * @Annotation
 */
class NotInArray extends Constraint
{
    public string $message = 'Value must not be neither of following: {{ array }}';

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingTraversableTypeHintSpecification
     */
    public array|Traversable|ArrayAccess $array = [];

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions(): array
    {
        return [
            'array',
        ];
    }
}
