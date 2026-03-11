<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PhoneNumberPrefixConsistencyValidator extends ConstraintValidator
{
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        if (!$constraint instanceof PhoneNumberPrefixConsistency) {
            throw new UnexpectedTypeException($constraint, PhoneNumberPrefixConsistency::class);
        }

        if (!$value instanceof PhoneData) {
            throw new UnexpectedTypeException($value, PhoneData::class);
        }

        $hasNumber = $value->number !== null && $value->number !== '';
        $hasPrefix = $value->prefix !== null;

        if ($hasNumber && !$hasPrefix) {
            $this->context->buildViolation(t('Please enter phone prefix'))
                ->atPath('countryCode')
                ->addViolation();
        }
    }
}
