<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use DateTimeImmutable;
use DateTimeInterface;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Form\DatePickerType;

/**
 * A filter over a date — a day picked in the calendar of the administration. Over a date and time the day
 * is a range in the time zone of the administration: "is 12.5." means from its midnight to the last moment
 * before the midnight after, "before 12.5." means before its midnight, "after 12.5." means from the midnight
 * after. The medium compares the moment, whatever time zone it stores the time in.
 */
class DateFilter extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getDefaultOperators(): array
    {
        return [
            ExpressionOperatorEnum::EQUALS,
            ExpressionOperatorEnum::LESS_THAN,
            ExpressionOperatorEnum::GREATER_THAN,
            ExpressionOperatorEnum::BETWEEN,
            ExpressionOperatorEnum::IS_NULL,
            ExpressionOperatorEnum::IS_NOT_NULL,
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getOperatorLabel(string $operator): string
    {
        return match ($operator) {
            ExpressionOperatorEnum::LESS_THAN => t('is before'),
            ExpressionOperatorEnum::GREATER_THAN => t('is after'),
            default => parent::getOperatorLabel($operator),
        };
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormType(string $arity): string
    {
        return DatePickerType::class;
    }

    /**
     * The picked day is kept in the time zone of the administration, so that the arithmetic of the day
     * bounds follows its calendar (a day with a DST change is not 24 hours long).
     *
     * {@inheritdoc}
     */
    #[Override]
    protected function getDefaultValueFormOptions(string $arity): array
    {
        return [
            'model_timezone' => $this->getEnvironment()->displayTimeZone->getName(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getApplicableValueTypes(): array
    {
        return [PathValueTypeEnum::DATE, PathValueTypeEnum::DATETIME];
    }

    /**
     * A day is a single comparison of a range, never two comparisons of its bounds — over a collection each
     * comparison would be matched by a related row of its own, so the end of one day and the start of another
     * would make a day nobody picked.
     *
     * {@inheritdoc}
     */
    #[Override]
    protected function createComparison(string $operator, mixed $value): ?ConditionInterface
    {
        if ($this->isDateOnly()) {
            return parent::createComparison($operator, $value);
        }

        return match ($operator) {
            ExpressionOperatorEnum::EQUALS => Condition::between($this->path, DateTimeImmutable::createFromInterface($value), $this->endOfDay($value)),
            ExpressionOperatorEnum::LESS_THAN => Condition::lessThan($this->path, DateTimeImmutable::createFromInterface($value)),
            ExpressionOperatorEnum::GREATER_THAN => Condition::greaterThanOrEqual($this->path, $this->nextDay($value)),
            ExpressionOperatorEnum::BETWEEN => Condition::between($this->path, DateTimeImmutable::createFromInterface($value[0]), $this->endOfDay($value[1])),
            default => parent::createComparison($operator, $value),
        };
    }

    /**
     * A column holding a date without time is compared with the picked day as it is; a column holding
     * a date and time (or a column the medium cannot describe) by the bounds of the day.
     */
    protected function isDateOnly(): bool
    {
        return $this->pathDescription?->valueType === PathValueTypeEnum::DATE;
    }

    protected function nextDay(DateTimeInterface $day): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface($day)->modify('+1 day');
    }

    /**
     * The last moment a medium tells apart before the next day — a database storing whole seconds drops the
     * fraction, so the day ends with its last second there.
     */
    protected function endOfDay(DateTimeInterface $day): DateTimeImmutable
    {
        return $this->nextDay($day)->modify('-1 microsecond');
    }
}
