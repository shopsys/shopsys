<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class Email extends Constraint
{
    public string $message = 'This value is not a valid email address.';
}
