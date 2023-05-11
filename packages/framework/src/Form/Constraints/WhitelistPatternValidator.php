<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class WhitelistPatternValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof WhitelistPattern) {
            throw new UnexpectedTypeException($constraint, WhitelistPattern::class);
        }

        if ($value === null) {
            $this->context->addViolation($constraint->blankMessage);

            return;
        }

        if (@preg_match($value, '') === false) {
            $this->context->addViolation($constraint->message);
        }
    }
}
