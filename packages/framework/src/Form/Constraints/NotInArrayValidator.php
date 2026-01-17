<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use ArrayAccess;
use Override;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Traversable;

class NotInArrayValidator extends ConstraintValidator
{
    /**
     * @param string $value
     */
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
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
