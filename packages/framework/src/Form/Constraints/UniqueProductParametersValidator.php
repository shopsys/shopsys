<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueProductParametersValidator extends ConstraintValidator
{
    /**
     * @param array $values
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($values, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueProductParameters) {
            throw new UnexpectedTypeException($constraint, UniqueCollection::class);
        }

        $uniqueValues = [];
        $violations = [];

        /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData $value */
        foreach ($values as $value) {
            $parameterId = $value->parameter->getId();
            $uniqueKey = $parameterId . '-' . $value->parameterValueData->locale;

            if (array_key_exists($uniqueKey, $uniqueValues)) {
                $violations[$parameterId] = $value->parameter->getName();
            }

            $uniqueValues[$uniqueKey] = $parameterId;
        }

        foreach ($violations as $violation) {
            $this->context->addViolation(
                $constraint->message,
                [
                    '{{ parameterName }}' => $violation,
                ],
            );
        }
    }
}
