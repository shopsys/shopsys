<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class RequiredPair extends Constraint
{
    public string $field1;

    public string $field2;

    public string $message = 'Please fill both %field1% and %field2%';
}
