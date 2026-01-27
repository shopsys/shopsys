<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Symfony\Component\Form\FormTypeInterface;

class OrderCreateDateFilter implements AdvancedSearchFilterInterface
{
    public const string NAME = 'orderCreatedAt';

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_AFTER,
            self::OPERATOR_BEFORE,
            self::OPERATOR_IS,
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormType(): FormTypeInterface|string
    {
        return DatePickerType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormOptions(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, array $rulesData): void
    {
        foreach ($rulesData as $index => $ruleData) {
            if ($ruleData->value === null) {
                continue;
            }

            /** @var \DateTimeImmutable $inputDate */
            $inputDate = $ruleData->value;

            $parameterName = 'orderCreatedAt_' . $index;
            $parameterName2 = 'orderCreatedAt_' . $index . '_2';

            if ($ruleData->operator === self::OPERATOR_BEFORE) {
                $queryBuilder->andWhere('o.createdAt < :' . $parameterName)
                    ->setParameter($parameterName, $inputDate);
            } elseif ($ruleData->operator === self::OPERATOR_AFTER) {
                $queryBuilder->andWhere('o.createdAt >= :' . $parameterName)
                    ->setParameter($parameterName, $inputDate);
            } elseif ($ruleData->operator === self::OPERATOR_IS) {
                $dateDayAfter = $inputDate->modify('+1 day');

                $queryBuilder->andWhere('o.createdAt BETWEEN :' . $parameterName . ' AND :' . $parameterName2)
                    ->setParameter($parameterName, $inputDate)
                    ->setParameter($parameterName2, $dateDayAfter);
            }
        }
    }
}
