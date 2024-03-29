<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class DeliveryAddressOfCurrentCustomer extends Constraint
{
    public string $message = 'Selected delivery address no longer exists';
}
