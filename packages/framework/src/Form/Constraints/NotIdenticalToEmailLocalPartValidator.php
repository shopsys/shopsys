<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NotIdenticalToEmailLocalPartValidator extends ConstraintValidator
{
    /**
     * @param array $values
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($values, Constraint $constraint)
    {
        if (!$constraint instanceof NotIdenticalToEmailLocalPart) {
            throw new UnexpectedTypeException($constraint, NotIdenticalToEmailLocalPart::class);
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $password = $propertyAccessor->getValue($values, $constraint->password);
        $email = $propertyAccessor->getValue($values, $constraint->email);

        if ($password === null || $email === null) {
            return;
        }

        if (str_starts_with($email, $password . '@')) {
            $this->context->buildViolation($constraint->message)
                ->atPath($constraint->errorPath)
                ->addViolation();
        }
    }
}
