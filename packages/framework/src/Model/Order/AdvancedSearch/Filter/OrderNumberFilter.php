<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AbstractAdvancedSearchFilter;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\OrderAdvancedSearchFacade;

class OrderNumberFilter extends AbstractAdvancedSearchFilter
{
    public const NAME = 'orderNumber';

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
        return t('Order number');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, array $rulesData): void
    {
        foreach ($rulesData as $index => $ruleData) {
            if (
                $ruleData->operator === self::OPERATOR_CONTAINS
                || $ruleData->operator === self::OPERATOR_NOT_CONTAINS
            ) {
                $searchValue = $this->getSearchValue($ruleData);
                $dqlOperator = $this->getDqlOperator($ruleData->operator);
                $parameterName = 'orderNumber_' . $index;
                $queryBuilder->andWhere('o.number ' . $dqlOperator . ' :' . $parameterName);
                $queryBuilder->setParameter($parameterName, $searchValue);
            }
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
