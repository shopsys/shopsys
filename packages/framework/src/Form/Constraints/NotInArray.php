<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NotInArray extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Value must not be neither of following: {{ array }}';

    /**
     * @var array|\Traversable|\ArrayAccess
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingTraversableTypeHintSpecification
     */
    public $array = [];

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
