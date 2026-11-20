<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PickupPlaceOpeningHoursSizeValidator extends ConstraintValidator
{
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PickupPlaceOpeningHoursSize) {
            throw new UnexpectedTypeException($constraint, PickupPlaceOpeningHoursSize::class);
        }

        $openingHoursOfDays = $value->openingHoursOfDays;
        $exceptionDays = $value->exceptionDays;

        if (count($openingHoursOfDays) > $constraint->maxDays) {
            $this->context->buildViolation($constraint->tooManyDaysMessage)
                ->setParameter('{{ limit }}', (string)$constraint->maxDays)
                ->setCode(PickupPlaceOpeningHoursSize::TOO_MANY_DAYS_ERROR)
                ->atPath('openingHoursOfDays')
                ->addViolation();
        }

        if (count($exceptionDays) > $constraint->maxExceptionDays) {
            $this->context->buildViolation($constraint->tooManyExceptionDaysMessage)
                ->setParameter('{{ limit }}', (string)$constraint->maxExceptionDays)
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
            if (count($day->openingHoursRanges) <= $constraint->maxOpeningHoursRangesOfDay) {
                continue;
            }

            $this->context->buildViolation($constraint->tooManyOpeningHoursRangesMessage)
                ->setParameter('{{ limit }}', (string)$constraint->maxOpeningHoursRangesOfDay)
                ->setCode(PickupPlaceOpeningHoursSize::TOO_MANY_OPENING_HOURS_RANGES_ERROR)
                ->atPath(sprintf('%s[%d].openingHoursRanges', $daysPath, $index))
                ->addViolation();
        }
    }
}
