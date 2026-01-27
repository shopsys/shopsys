<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchFilterOperatorNotFoundException;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AbstractAdvancedSearchFilter;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\OrderAdvancedSearchFacade;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormTypeInterface;

class OrderPriceFilterWithVatFilter extends AbstractAdvancedSearchFilter
{
    public const string NAME = 'orderTotalPriceWithVat';

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
        return t('Price including VAT');
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormType(): FormTypeInterface|string
    {
        return NumberType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, array $rulesData): void
    {
        foreach ($rulesData as $index => $ruleData) {
            try {
                $dqlOperator = $this->getDqlOperator($ruleData->operator);
            } catch (AdvancedSearchFilterOperatorNotFoundException) {
                continue;
            }

            if ($ruleData->value === '' || $ruleData->value === null) {
                continue;
            }
            $searchValue = $ruleData->value;
            $parameterName = 'totalPriceWithVat_' . $index;
            $queryBuilder->andWhere('o.totalPriceWithVat ' . $dqlOperator . ' :' . $parameterName);
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
