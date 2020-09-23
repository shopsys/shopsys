<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Form\Exception\NotMoneyTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NotNegativeMoneyAmountValidator extends ConstraintValidator
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof NotNegativeMoneyAmount) {
            throw new UnexpectedTypeException($constraint, NotNegativeMoneyAmount::class);
        }

        if ($value === null) {
            return;
        }

        if (!($value instanceof Money)) {
            throw new NotMoneyTypeException($value);
        }

        if ($value->isNegative()) {
            $this->context->addViolation($constraint->message);
        }
    }
}
