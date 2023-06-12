<?php

declare(strict_types=1);

namespace App\Form\Constraints;

use App\Model\Product\ProductFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueProductCatnumValidator extends ConstraintValidator
{
    /**
     * @param \App\Model\Product\ProductFacade $productFacade
     */
    public function __construct(private ProductFacade $productFacade)
    {
    }

    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueProductCatnum) {
            throw new UnexpectedTypeException($constraint, UniqueProductCatnum::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if ($constraint->product !== null && $value === $constraint->product->getCatnum()) {
            return;
        }

        $productByCatnum = $this->productFacade->findByCatnum($value);

        if ($productByCatnum === null) {
            return;
        }

        $this->context->addViolation($constraint->message);
    }
}
