<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ProductUponInquiryFilter implements AdvancedSearchFilterInterface
{
    public const string NAME = 'productUponInquiryFilter';

    #[Override]
    public function getName(): string
    {
        return self::NAME;
    }

    #[Override]
    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_IS,
            self::OPERATOR_IS_NOT,
        ];
    }

    #[Override]
    public function getValueFormType(): string
    {
        return HiddenType::class;
    }

    #[Override]
    public function getValueFormOptions(): array
    {
        return [];
    }

    #[Override]
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
