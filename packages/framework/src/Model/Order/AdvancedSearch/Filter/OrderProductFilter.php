<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Form\ProductType;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchFilterOperatorNotFoundException;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AbstractAdvancedSearchFilter;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\OrderAdvancedSearchFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Symfony\Component\Form\FormTypeInterface;

class OrderProductFilter extends AbstractAdvancedSearchFilter
{
    public const string NAME = 'orderProduct';

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
    public function getValueFormType(): FormTypeInterface|string
    {
        return ProductType::class;
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
                /** @var \Shopsys\FrameworkBundle\Model\Product\Product|null $searchValue */
                $searchValue = $ruleData->value;

                if ($searchValue === null) {
                    continue;
                }
                $dqlOperator = $this->getContainsDqlOperator($ruleData->operator);
                $parameterName = 'orderProduct_' . $index;
                $tableAlias = 'oi_' . $index;
                $queryBuilder->andWhere(
                    $dqlOperator . ' (SELECT 1 FROM ' . OrderItem::class . ' ' . $tableAlias . ' ' .
                        'WHERE ' . $tableAlias . '.order = o AND ' . $tableAlias . '.product = :' . $parameterName . ')',
                );
                $queryBuilder->setParameter($parameterName, $searchValue);
            }
        }
    }

    protected function getContainsDqlOperator(string $operator): string
    {
        switch ($operator) {
            case self::OPERATOR_CONTAINS:
                return 'EXISTS';
            case self::OPERATOR_NOT_CONTAINS:
                return 'NOT EXISTS';
        }

        throw new AdvancedSearchFilterOperatorNotFoundException($operator);
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
