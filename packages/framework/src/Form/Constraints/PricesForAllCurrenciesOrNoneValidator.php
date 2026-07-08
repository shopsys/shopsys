<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PricesForAllCurrenciesOrNoneValidator extends ConstraintValidator
{
    /**
     * @param array<string, \Shopsys\FrameworkBundle\Component\Money\Money|null>|null $value
     */
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PricesForAllCurrenciesOrNone) {
            throw new UnexpectedTypeException($constraint, PricesForAllCurrenciesOrNone::class);
        }

        if ($value === null) {
            return;
        }

        $filledPricesCount = count(array_filter($value, static fn ($price) => $price !== null));

        if ($filledPricesCount !== 0 && $filledPricesCount !== count($value)) {
            $this->context->addViolation($constraint->message);
        }
    }
}
