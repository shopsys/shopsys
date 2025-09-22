<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormTypeInterface;

class ProductHasPromotionXyFilter implements AdvancedSearchFilterInterface
{
    public const string NAME = 'productHasPromotionXy';

    /**
     * @return string
     */
    #[Override]
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return array
     */
    #[Override]
    public function getAllowedOperators(): array
    {
        return [self::OPERATOR_IS, self::OPERATOR_IS_NOT];
    }

    /**
     * @return \Symfony\Component\Form\FormTypeInterface|string
     */
    #[Override]
    public function getValueFormType(): FormTypeInterface|string
    {
        return HiddenType::class;
    }

    /**
     * @return array
     */
    #[Override]
    public function getValueFormOptions(): array
    {
        return [];
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchRuleData[] $rulesData
     */
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, array $rulesData): void
    {
        foreach ($rulesData as $ruleData) {
            if ($ruleData->operator === self::OPERATOR_IS) {
                $queryBuilder->leftJoin('p.promotionXy', 'pxy');
                $queryBuilder->andWhere('pxy.buyQuantity IS NOT NULL AND pxy.freeQuantity IS NOT NULL');
            }

            if ($ruleData->operator !== self::OPERATOR_IS_NOT) {
                continue;
            }

            $queryBuilder->leftJoin('p.promotionXy', 'pxy');
            $queryBuilder->andWhere('pxy.buyQuantity IS NULL AND pxy.freeQuantity IS NULL');
        }
    }
}
