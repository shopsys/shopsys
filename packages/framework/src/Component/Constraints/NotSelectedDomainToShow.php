<?php

namespace Shopsys\FrameworkBundle\Component\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NotSelectedDomainToShow extends Constraint
{
    public $message = 'You have to select any domain.';
}
