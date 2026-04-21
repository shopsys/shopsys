<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PhoneNumberValidator extends ConstraintValidator
{
    protected const int MAX_NUMBER_LENGTH = 30;

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        $this->assertValidationInput($value, $constraint);

        $phoneNumber = $value->number;

        if ($phoneNumber === null) {
            return;
        }

        if (mb_strlen($phoneNumber) > static::MAX_NUMBER_LENGTH) {
            $this->context->buildViolation(
                t('Phone number {{ value }} is too long. Maximum length is {{ limit }} characters.'),
                [
                    '{{ value }}' => $this->formatValue($phoneNumber),
                    '{{ limit }}' => $this->formatValue(static::MAX_NUMBER_LENGTH),
                ],
            )
                ->atPath('number')
                ->addViolation();
        }
    }

    protected function assertValidationInput(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PhoneNumber) {
            throw new UnexpectedTypeException($constraint, PhoneNumber::class);
        }

        if (!$value instanceof PhoneData) {
            throw new UnexpectedTypeException($value, PhoneData::class);
        }
    }
}
