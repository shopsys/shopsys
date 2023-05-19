<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueEmail extends Constraint
{
    public string $message = 'Email {{ email }} is already registered';

    public ?string $ignoredEmail = null;

    public ?int $domainId = null;
}
