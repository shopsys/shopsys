<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ProductStockFilter implements AdvancedSearchFilterInterface
{
    public const NAME = 'productStock';

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_IS_USED,
            self::OPERATOR_IS_NOT_USED,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getValueFormType(): string|\Symfony\Component\Form\FormTypeInterface
    {
        return HiddenType::class;
    }

    /**
     * {@inheritdoc}
     * @return mixed[]
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
            $usingStock = $ruleData->operator === self::OPERATOR_IS_USED;

            $parameterName = 'usingStock_' . $index;
            $queryBuilder->andWhere('p.usingStock = :' . $parameterName)
                ->setParameter($parameterName, $usingStock);
        }
    }
}
