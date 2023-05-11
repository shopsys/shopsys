<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NotSelectedDomainToShow extends Constraint
{
    /**
     * @var string
     */
    public $message = 'You have to select any domain.';
}
