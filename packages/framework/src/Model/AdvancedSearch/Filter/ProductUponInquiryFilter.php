<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ProductUponInquiryFilter implements AdvancedSearchFilterInterface
{
    public const string NAME = 'productUponInquiryFilter';

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
            self::OPERATOR_IS,
            self::OPERATOR_IS_NOT,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getValueFormType(): string
    {
        return HiddenType::class;
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
            $operator = $ruleData->operator === self::OPERATOR_IS ? '=' : '!=';

            $parameterName = 'productType_' . $index;
            $queryBuilder
                ->andWhere('p.productType ' . $operator . ' :' . $parameterName)
                ->setParameter($parameterName, ProductTypeEnum::TYPE_INQUIRY);
        }
    }
}
