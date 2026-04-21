<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AbstractAdvancedSearchFilter;
use Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch\ComplaintAdvancedSearchFacade;

abstract class AbstractComplaintContainsFilter extends AbstractAdvancedSearchFilter
{
    abstract protected function getFieldName(): string;

    protected function getDqlFieldExpression(): string
    {
        return 'cmp.' . $this->getFieldName();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, array $rulesData): void
    {
        foreach ($rulesData as $index => $ruleData) {
            $searchValue = $this->getSearchValue($ruleData);
            $dqlOperator = $this->getDqlOperator($ruleData->operator);
            $parameterName = $this->getFieldName() . '_' . $index;
            $queryBuilder->andWhere(
                'NORMALIZED(' . $this->getDqlFieldExpression() . ') ' . $dqlOperator . ' NORMALIZED(:' . $parameterName . ')',
            );
            $queryBuilder->setParameter($parameterName, $searchValue);
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
