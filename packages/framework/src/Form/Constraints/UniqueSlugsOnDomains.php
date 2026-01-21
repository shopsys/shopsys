<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueSlugsOnDomains extends Constraint
{
    public string $message = 'Address {{ url }} already exists.';

    public string $messageDuplicate = 'Address {{ url }} can be entered only once.';
}
