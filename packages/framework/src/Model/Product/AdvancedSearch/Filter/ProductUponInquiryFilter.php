<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ProductUponInquiryFilter implements AdvancedSearchFilterInterface
{
    public const string NAME = 'productUponInquiryFilter';

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
    #[Override]
    public function getValueFormType(): string
    {
        return HiddenType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormOptions(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, array $rulesData): void
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
