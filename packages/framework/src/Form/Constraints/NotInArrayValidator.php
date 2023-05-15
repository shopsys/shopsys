<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use ArrayAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Traversable;

class NotInArrayValidator extends ConstraintValidator
{
    /**
     * @param string $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof NotInArray) {
            throw new UnexpectedTypeException($constraint, NotInArray::class);
        }

        if (
            !is_array($constraint->array)
            && !(
                $constraint->array instanceof Traversable
                && $constraint->array instanceof ArrayAccess
            )
        ) {
            throw new UnexpectedTypeException(
                $constraint->array,
                'array or Traversable and ArrayAccess',
            );
        }

        if (in_array($value, $constraint->array, false)) {
            $this->context->addViolation(
                $constraint->message,
                [
                    '{{ array }}' => implode(', ', $constraint->array),
                ],
            );
        }
    }
}
