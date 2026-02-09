<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Translation\Resources;

use Symfony\Component\Validator\Constraint;

class ConstraintWithPromotedProperties extends Constraint
{
    public function __construct(
        public string $message = 'Promoted message will be extracted.',
        public string $otherMessage = 'Another promoted message.',
        public string $differentProperty = 'This will not be extracted.',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }
}
