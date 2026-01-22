<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Translation\Resources;

use Symfony\Component\Validator\Constraints;

class ConstraintUsageClass
{
    public function constraintsWithMessages(): void
    {
        new Constraints\NotBlank(message: 'NotBlank message');
        new Constraints\Length(
            minMessage: 'Length min message',
            maxMessage: 'Length max message',
            exactMessage: 'Length exact message',
        );
        new Constraints\GreaterThan(
            value: 0,
            message: 'GreaterThan message',
        );
    }

    public function constraintsWithPositionalArguments(): void
    {
        new Constraints\NotBlank(null, 'Positional NotBlank message');
        new Constraints\Length(null, 5, 100, null, null, null, null, 'Positional Length min message', 'Positional Length max message');
        new Constraints\GreaterThan(0, null, 'Positional GreaterThan message');
    }

    public function constraintsWithMixedPositionalAndNamed(): void
    {
        new Constraints\Length(null, 5, minMessage: 'Mixed Length min message');
        new Constraints\GreaterThan(0, message: 'Mixed GreaterThan message');
    }
}
