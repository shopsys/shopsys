<?php

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\Filter;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class OrderPriceFilterWithVatFilter implements AdvancedSearchFilterInterface
{
/**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'orderTotalPriceWithVat';
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getValueFormType()
    {
        return NumberType::class;
    }

/**
     * {@inheritdoc}
     */
    public function getValueFormOptions():array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function extendQueryBuilder(QueryBuilder $queryBuilder, $rulesData): void
    {
        foreach ($rulesData as $index => $ruleData) {
            $dqlOperator = $this->getContainsDqlOperator($ruleData->operator);
            if ($dqlOperator === null || $ruleData->value == '' || $ruleData->value === null) {
                continue;
            }
            $searchValue = $ruleData->value;
            $parameterName = 'totalPriceWithVat_' . $index;
            $queryBuilder->andWhere('o.totalPriceWithVat ' . $dqlOperator . ' :' . $parameterName);
            $queryBuilder->setParameter($parameterName, $searchValue);
        }
    }

    private function getContainsDqlOperator(string $operator): string
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
