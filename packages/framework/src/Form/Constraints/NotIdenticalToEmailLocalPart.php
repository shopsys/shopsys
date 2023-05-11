<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class NotIdenticalToEmailLocalPart extends Constraint
{
    public string $password;

    public string $email;

    public string $errorPath;

    public string $message = 'Password cannot be local part of email.';
}
