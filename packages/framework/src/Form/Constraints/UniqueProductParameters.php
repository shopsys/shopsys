<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueProductParameters extends Constraint
{
    public string $message = 'Product parameters are duplicate.';
}
