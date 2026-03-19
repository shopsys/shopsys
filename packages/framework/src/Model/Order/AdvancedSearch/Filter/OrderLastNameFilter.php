<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AbstractAdvancedSearchFilter;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\OrderAdvancedSearchFacade;

class OrderLastNameFilter extends AbstractAdvancedSearchFilter
{
    public const string NAME = 'customerLastName';

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
        return t('Customer last name');
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
            $parameterName = 'lastName_' . $index;
            $queryBuilder->andWhere(
                'NORMALIZED(o.lastName) ' . $dqlOperator . ' NORMALIZED(:' . $parameterName . ') OR NORMALIZED(o.deliveryLastName) ' . $dqlOperator . ' NORMALIZED(:' . $parameterName . ')',
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
        return OrderAdvancedSearchFacade::getEntityType();
    }
}
