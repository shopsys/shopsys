<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

class PricesForAllCurrenciesOrNone extends Constraint
{
    /**
     * @param array<string>|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        public string $message = 'Enter the price for every currency of the domain, or leave all prices empty.',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }
}
