<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class MustUploadFile extends Constraint
{
    public string $message = 'Please upload a file.';
}
