<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AbstractAdvancedSearchFilter;
use Shopsys\FrameworkBundle\Model\Customer\User\AdvancedSearch\CustomerUserAdvancedSearchFacade;

abstract class AbstractCustomerUserCompanyContainsFilter extends AbstractAdvancedSearchFilter
{
    abstract protected function getFieldName(): string;

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
                'NORMALIZED(ba.' . $this->getFieldName() . ') ' . $dqlOperator . ' NORMALIZED(:' . $parameterName . ')',
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
        return CustomerUserAdvancedSearchFacade::getEntityType();
    }
}
