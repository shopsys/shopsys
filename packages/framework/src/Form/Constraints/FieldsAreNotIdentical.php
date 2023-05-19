<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class FieldsAreNotIdentical extends Constraint
{
    public string $field1;

    public string $field2;

    public string $errorPath;

    public string $message = 'Fields must not be identical';
}
