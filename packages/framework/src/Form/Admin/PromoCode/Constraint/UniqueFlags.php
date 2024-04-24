<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PromoCode\Constraint;

use Symfony\Component\Validator\Constraint;

class UniqueFlags extends Constraint
{
    public string $message = 'Flag "{{ flagName }}" is selected multiple times.';
}
