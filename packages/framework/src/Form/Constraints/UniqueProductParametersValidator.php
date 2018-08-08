<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueProductParametersValidator extends ConstraintValidator
{
    public function validate(array $values, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueProductParameters) {
            throw new \Symfony\Component\Validator\Exception\UnexpectedTypeException($constraint, UniqueCollection::class);
        }

        // Dummy validator, because validator is implemented in JS and
        // \Shopsys\FrameworkBundle\Form\Transformers\ProductParameterValueToProductParameterValuesLocalizedTransformer
        // throw exception on duplicate parameters
    }
}
