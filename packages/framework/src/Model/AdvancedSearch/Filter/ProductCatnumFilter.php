<?php

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProductCatnumFilter implements AdvancedSearchFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'productCatnum';
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_CONTAINS,
            self::OPERATOR_NOT_CONTAINS,
            self::OPERATOR_NOT_SET,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getValueFormType()
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getValueFormOptions(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function extendQueryBuilder(QueryBuilder $queryBuilder, $rulesData): void
    {
        foreach ($rulesData as $index => $ruleData) {
            if ($ruleData->operator === self::OPERATOR_NOT_SET) {
                $queryBuilder->andWhere('p.catnum IS NULL');
            } elseif ($ruleData->operator === self::OPERATOR_CONTAINS || $ruleData->operator === self::OPERATOR_NOT_CONTAINS) {
                if ($ruleData->value === null) {
                    $searchValue = '%';
                } else {
                    $searchValue = DatabaseSearching::getFullTextLikeSearchString($ruleData->value);
                }

                $dqlOperator = $this->getContainsDqlOperator($ruleData->operator);
                $parameterName = 'productCatnum_' . $index;
                $queryBuilder->andWhere('NORMALIZE(p.catnum) ' . $dqlOperator . ' NORMALIZE(:' . $parameterName . ')');
                $queryBuilder->setParameter($parameterName, $searchValue);
            }
        }
    }

    private function getContainsDqlOperator(string $operator): string
    {
        switch ($operator) {
            case self::OPERATOR_CONTAINS:
                return 'LIKE';
            case self::OPERATOR_NOT_CONTAINS:
                return 'NOT LIKE';
        }
    }
}
