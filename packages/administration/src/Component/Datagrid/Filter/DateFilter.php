<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Form\DatePickerType;

/**
 * A filter over a date — a day picked in the calendar of the administration. Over a date and time the day
 * is a range in the time zone of the administration: "is 12.5." means from its midnight to the midnight
 * after, "before 12.5." means before its midnight, "after 12.5." means from the midnight after.
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
    public function getValueFormType(string $operator): string
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
    protected function getDefaultValueFormOptions(string $operator): array
    {
        return [
            'model_timezone' => $this->getEnvironment()->displayTimeZone->getName(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function createComparison(string $operator, mixed $value): ?ConditionInterface
    {
        if ($this->isDateOnly()) {
            return parent::createComparison($operator, $value);
        }

        return match ($operator) {
            ExpressionOperatorEnum::EQUALS => Condition::andX(
                Condition::greaterThanOrEqual($this->path, $this->toStoredTime($value)),
                Condition::lessThan($this->path, $this->toStoredTime($this->nextDay($value))),
            ),
            ExpressionOperatorEnum::LESS_THAN => Condition::lessThan($this->path, $this->toStoredTime($value)),
            ExpressionOperatorEnum::GREATER_THAN => Condition::greaterThanOrEqual($this->path, $this->toStoredTime($this->nextDay($value))),
            ExpressionOperatorEnum::BETWEEN => Condition::andX(
                Condition::greaterThanOrEqual($this->path, $this->toStoredTime($value[0])),
                Condition::lessThan($this->path, $this->toStoredTime($this->nextDay($value[1]))),
            ),
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
     * The database stores the time in the default time zone of the application, and Doctrine binds a
     * parameter as it is written in its own time zone, so the bound is converted before it is compared.
     */
    protected function toStoredTime(DateTimeInterface $dateTime): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface($dateTime)->setTimezone(new DateTimeZone(date_default_timezone_get()));
    }
}
