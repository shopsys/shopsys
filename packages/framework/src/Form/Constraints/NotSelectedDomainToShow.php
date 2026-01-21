<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class NotSelectedDomainToShow extends Constraint
{
    public string $message = 'You have to select any domain.';
}
