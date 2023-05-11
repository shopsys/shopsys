<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueEmail extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Email {{ email }} is already registered';

    /**
     * @var string|null
     */
    public $ignoredEmail = null;

    /**
     * @var int|null
     */
    public $domainId = null;
}
