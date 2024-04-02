<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PromoCode\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueFlagsValidator extends ConstraintValidator
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag[] $values
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($values, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueFlags) {
            throw new UnexpectedTypeException($constraint, UniqueFlags::class);
        }

        $uniqueValues = [];
        $violations = [];

        foreach ($values as $promoCodeFlag) {
            if ($promoCodeFlag === null) {
                continue;
            }

            $flagId = $promoCodeFlag->getFlag()->getId();

            if (array_key_exists($flagId, $uniqueValues)) {
                $violations[$flagId] = $promoCodeFlag->getFlag()->getName();
            }

            $uniqueValues[$flagId] = $flagId;
        }

        foreach ($violations as $violation) {
            $this->context->addViolation(
                $constraint->message,
                [
                    '{{ flagName }}' => $violation,
                ],
            );
        }
    }
}
