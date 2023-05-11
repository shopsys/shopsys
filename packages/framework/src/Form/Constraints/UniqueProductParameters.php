<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueProductParameters extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Product parameters are duplicate.';
}
