<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AbstractAdvancedSearchFilter;
use Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch\ComplaintAdvancedSearchFacade;

class ComplaintCreateDateFilter extends AbstractAdvancedSearchFilter
{
    public const string NAME = 'complaintCreatedAt';

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
    public function getLabel(): string
    {
        return t('Created on');
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
    public function getValueFormType(): string
    {
        return DatePickerType::class;
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

            $parameterName = 'complaintCreatedAt_' . $index;
            $parameterName2 = 'complaintCreatedAt_' . $index . '_2';

            if ($ruleData->operator === self::OPERATOR_BEFORE) {
                $queryBuilder->andWhere('cmp.createdAt < :' . $parameterName)
                    ->setParameter($parameterName, $inputDate);
            } elseif ($ruleData->operator === self::OPERATOR_AFTER) {
                $queryBuilder->andWhere('cmp.createdAt >= :' . $parameterName)
                    ->setParameter($parameterName, $inputDate);
            } elseif ($ruleData->operator === self::OPERATOR_IS) {
                $dateDayAfter = $inputDate->modify('+1 day');

                $queryBuilder->andWhere('cmp.createdAt BETWEEN :' . $parameterName . ' AND :' . $parameterName2)
                    ->setParameter($parameterName, $inputDate)
                    ->setParameter($parameterName2, $dateDayAfter);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getEntityType(): string
    {
        return ComplaintAdvancedSearchFacade::getEntityType();
    }
}
