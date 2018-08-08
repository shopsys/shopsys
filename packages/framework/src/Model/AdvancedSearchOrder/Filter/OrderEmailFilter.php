<?php

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\Filter;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class OrderEmailFilter implements AdvancedSearchFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'customerEmail';
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedOperators(): array
{
        return [
            self::OPERATOR_CONTAINS,
            self::OPERATOR_NOT_CONTAINS,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getValueFormType()
    {
        return EmailType::class;
    }

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
            if ($ruleData->value === null || $ruleData->value == '') {
                $searchValue = '%';
            } else {
                $searchValue = DatabaseSearching::getFullTextLikeSearchString($ruleData->value);
            }
            $dqlOperator = $this->getContainsDqlOperator($ruleData->operator);
            $parameterName = 'email_' . $index;
            $queryBuilder->andWhere('NORMALIZE(o.email) ' . $dqlOperator . ' NORMALIZE(:' . $parameterName . ')');
            $queryBuilder->setParameter($parameterName, $searchValue);
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
