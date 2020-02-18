<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class DeliveryAddressOfCurrentCustomer extends Constraint
{
    public $message = 'Selected delivery address no longer exists';
}
