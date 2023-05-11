<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class FieldsAreNotIdentical extends Constraint
{
    /**
     * @var string
     */
    public $field1;

    /**
     * @var string
     */
    public $field2;

    /**
     * @var string
     */
    public $errorPath;

    /**
     * @var string
     */
    public $message = 'Fields must not be identical';
}
