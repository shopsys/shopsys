<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AbstractAdvancedSearchFilter;
use Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\ProductAdvancedSearchFacade;

class ProductPartnoFilter extends AbstractAdvancedSearchFilter
{
    public const string NAME = 'productPartno';

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
        return t('PartNo (serial number)');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
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
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, array $rulesData): void
    {
        foreach ($rulesData as $index => $ruleData) {
            if ($ruleData->operator === self::OPERATOR_NOT_SET) {
                $queryBuilder->andWhere('p.partno IS NULL');
            } elseif (
                $ruleData->operator === self::OPERATOR_CONTAINS
                || $ruleData->operator === self::OPERATOR_NOT_CONTAINS
            ) {
                $searchValue = $this->getSearchValue($ruleData);
                $dqlOperator = $this->getDqlOperator($ruleData->operator);
                $parameterName = 'productPartno_' . $index;
                $queryBuilder->andWhere('NORMALIZED(p.partno) ' . $dqlOperator . ' NORMALIZED(:' . $parameterName . ')');
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
        return ProductAdvancedSearchFacade::getEntityType();
    }
}
