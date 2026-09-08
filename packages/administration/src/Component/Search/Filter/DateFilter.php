<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search\Filter;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use Override;
use Shopsys\AdministrationBundle\Component\Search\FilterRule;
use Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection;
use Shopsys\AdministrationBundle\Component\Search\Operator;
use Shopsys\FrameworkBundle\Form\DatePickerType;

/**
 * Date filter on a single datetime field.
 * The "is" operator matches the whole day of the selected date.
 */
final class DateFilter extends AbstractFieldFilter
{
    public static function create(string $name, string $label): self
    {
        return new self($name, $label);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getAllowedOperators(): array
    {
        return [Operator::IS, Operator::BEFORE, Operator::AFTER];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormType(): string
    {
        return DatePickerType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, FilterRuleCollection $rules): void
    {
        $fieldExpression = $this->getSingleFieldExpression();

        foreach ($rules as $rule) {
            $date = $this->getRuleDate($rule);

            switch ($rule->operator) {
                case Operator::BEFORE:
                    $queryBuilder
                        ->andWhere(sprintf('%s < :%s', $fieldExpression, $rule->param()))
                        ->setParameter($rule->param(), $date);

                    break;
                case Operator::AFTER:
                    $queryBuilder
                        ->andWhere(sprintf('%s >= :%s', $fieldExpression, $rule->param()))
                        ->setParameter($rule->param(), $date);

                    break;
                case Operator::IS:
                    // the selected date matches the whole day
                    $queryBuilder
                        ->andWhere(sprintf(
                            '%s >= :%s AND %s < :%s',
                            $fieldExpression,
                            $rule->param('from'),
                            $fieldExpression,
                            $rule->param('to'),
                        ))
                        ->setParameter($rule->param('from'), $date)
                        ->setParameter($rule->param('to'), $date->modify('+1 day'));

                    break;
                default:
                    throw new InvalidArgumentException(sprintf('Unsupported operator "%s".', $rule->operator->value));
            }
        }
    }

    private function getRuleDate(FilterRule $rule): DateTimeImmutable
    {
        if (!$rule->value instanceof DateTimeInterface) {
            throw new InvalidArgumentException(sprintf('Value of the "%s" filter rule must be a date.', $this->name));
        }

        return DateTimeImmutable::createFromInterface($rule->value)->setTime(0, 0);
    }
}
