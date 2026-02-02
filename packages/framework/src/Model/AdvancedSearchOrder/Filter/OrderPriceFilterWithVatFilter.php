<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormTypeInterface;

class OrderPriceFilterWithVatFilter implements AdvancedSearchFilterInterface
{
    public const string NAME = 'orderTotalPriceWithVat';

    #[Override]
    public function getName(): string
    {
        return self::NAME;
    }

    #[Override]
    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_GT,
            self::OPERATOR_LT,
            self::OPERATOR_GTE,
            self::OPERATOR_LTE,
            self::OPERATOR_IS,
        ];
    }

    #[Override]
    public function getValueFormType(): FormTypeInterface|string
    {
        return NumberType::class;
    }

    #[Override]
    public function getValueFormOptions(): array
    {
        return [];
    }

    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, array $rulesData): void
    {
        foreach ($rulesData as $index => $ruleData) {
            $dqlOperator = $this->getContainsDqlOperator($ruleData->operator);

            if ($dqlOperator === null || $ruleData->value === '' || $ruleData->value === null) {
                continue;
            }
            $searchValue = $ruleData->value;
            $parameterName = 'totalPriceWithVat_' . $index;
            $queryBuilder->andWhere('o.totalPriceWithVat ' . $dqlOperator . ' :' . $parameterName);
            $queryBuilder->setParameter($parameterName, $searchValue);
        }
    }

    protected function getContainsDqlOperator(string $operator): ?string
    {
        switch ($operator) {
            case self::OPERATOR_GT:
                return '>';
            case self::OPERATOR_LT:
                return '<';
            case self::OPERATOR_GTE:
                return '>=';
            case self::OPERATOR_LTE:
                return '<=';
            case self::OPERATOR_IS:
                return '=';
        }

        return null;
    }
}
