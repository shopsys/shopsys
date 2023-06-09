<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Translation\Resources;

use Symfony\Component\Validator\Constraint;

class ConstraintClass extends Constraint
{
    public string $message = 'This value will be extracted.';

    public string $otherMessage = 'This value will also be extracted.';

    public string $differentProperty = 'This value will not be extracted (not a message).';
}
