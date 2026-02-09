<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ContainsValidator extends ConstraintValidator
{
    /**
     * @param string $value
     */
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Contains) {
            throw new UnexpectedTypeException($constraint, Contains::class);
        }

        if (mb_strpos($value, $constraint->needle) === false) {
            $this->context->addViolation(
                $constraint->message,
                [
                    '{{ value }}' => $this->formatValue($value),
                    '{{ needle }}' => $this->formatValue($constraint->needle),
                ],
            );
        }
    }
}
