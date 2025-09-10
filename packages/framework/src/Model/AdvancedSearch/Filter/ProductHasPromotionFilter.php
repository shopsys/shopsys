<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormTypeInterface;

class ProductHasPromotionFilter implements AdvancedSearchFilterInterface
{
    public const string NAME = 'productHasPromotion';

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
        return ChoiceType::class;
    }

    /**
     * @return array
     */
    #[Override]
    public function getValueFormOptions(): array
    {
        return [
            'expanded' => false,
            'multiple' => false,
            'choices' => [
                t('Yes') => 1,
                t('No') => 0,
            ],
        ];
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchRuleData[] $rulesData
     */
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, array $rulesData): void
    {
        foreach ($rulesData as $ruleData) {
            if ($ruleData->operator === self::OPERATOR_IS && (int)$ruleData->value === 1) {
                $queryBuilder->andWhere('p.promotionX IS NOT NULL AND p.promotionY IS NOT NULL');
            }

            if ($ruleData->operator === self::OPERATOR_IS_NOT && (int)$ruleData->value === 1) {
                $queryBuilder->andWhere('p.promotionX IS NULL AND p.promotionY IS NULL');
            }
        }
    }
}
