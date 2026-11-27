<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PickupPlaceOpeningHoursSizeValidator extends ConstraintValidator
{
    protected const int MAX_DAYS = 7;
    protected const int MAX_EXCEPTION_DAYS = 100;
    protected const int MAX_OPENING_HOURS_RANGES_OF_DAY = 10;

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PickupPlaceOpeningHoursSize) {
            throw new UnexpectedTypeException($constraint, PickupPlaceOpeningHoursSize::class);
        }

        $openingHoursOfDays = $value->openingHoursOfDays;
        $exceptionDays = $value->exceptionDays;

        if (count($openingHoursOfDays) > static::MAX_DAYS) {
            $this->context->buildViolation($constraint->tooManyDaysMessage)
                ->setParameter('{{ limit }}', (string)static::MAX_DAYS)
                ->setCode(PickupPlaceOpeningHoursSize::TOO_MANY_DAYS_ERROR)
                ->atPath('openingHoursOfDays')
                ->addViolation();
        }

        if (count($exceptionDays) > static::MAX_EXCEPTION_DAYS) {
            $this->context->buildViolation($constraint->tooManyExceptionDaysMessage)
                ->setParameter('{{ limit }}', (string)static::MAX_EXCEPTION_DAYS)
                ->setCode(PickupPlaceOpeningHoursSize::TOO_MANY_EXCEPTION_DAYS_ERROR)
                ->atPath('exceptionDays')
                ->addViolation();
        }

        $this->checkOpeningHoursRangesCount($openingHoursOfDays, 'openingHoursOfDays', $constraint);
        $this->checkOpeningHoursRangesCount($exceptionDays, 'exceptionDays', $constraint);
    }

    /**
     * @param object[] $days
     */
    protected function checkOpeningHoursRangesCount(
        array $days,
        string $daysPath,
        PickupPlaceOpeningHoursSize $constraint,
    ): void {
        foreach ($days as $index => $day) {
            if (count($day->openingHoursRanges) <= static::MAX_OPENING_HOURS_RANGES_OF_DAY) {
                continue;
            }

            $this->context->buildViolation($constraint->tooManyOpeningHoursRangesMessage)
                ->setParameter('{{ limit }}', (string)static::MAX_OPENING_HOURS_RANGES_OF_DAY)
                ->setCode(PickupPlaceOpeningHoursSize::TOO_MANY_OPENING_HOURS_RANGES_ERROR)
                ->atPath(sprintf('%s[%d].openingHoursRanges', $daysPath, $index))
                ->addViolation();
        }
    }
}
