<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueCollection extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Values are duplicate.';

    /**
     * @var mixed[]|null
     */
    public $fields = null;

    /**
     * @var bool
     */
    public $allowEmpty = false;
}
