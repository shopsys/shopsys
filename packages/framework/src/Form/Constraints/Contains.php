<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Contains extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Field must contain {{ needle }}.';

    /**
     * @var string|null
     */
    public $needle = null;

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
