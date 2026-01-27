<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductDomain;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormTypeInterface;

class ProductHasPromotionXyFilter implements AdvancedSearchFilterInterface
{
    public const string NAME = 'productHasPromotionXy';

    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    #[Override]
    public function getName(): string
    {
        return self::NAME;
    }

    #[Override]
    public function getAllowedOperators(): array
    {
        return [self::OPERATOR_IS, self::OPERATOR_IS_NOT];
    }

    #[Override]
    public function getValueFormType(): FormTypeInterface|string
    {
        return HiddenType::class;
    }

    #[Override]
    public function getValueFormOptions(): array
    {
        return [];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchRuleData[] $rulesData
     */
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, array $rulesData): void
    {
        $existsDql = $this->em->createQueryBuilder()
            ->select('1')
            ->from(ProductDomain::class, 'pd_xy')
            ->join('pd_xy.promotionXy', 'pxy')
            ->where('pd_xy.product = p.id')
            ->andWhere('pxy.buyQuantity IS NOT NULL')
            ->andWhere('pxy.freeQuantity IS NOT NULL')
            ->getDQL();

        foreach ($rulesData as $ruleData) {
            if ($ruleData->operator === self::OPERATOR_IS) {
                $queryBuilder->andWhere('EXISTS (' . $existsDql . ')');
            }

            if ($ruleData->operator !== self::OPERATOR_IS_NOT) {
                continue;
            }

            $queryBuilder->andWhere('NOT EXISTS (' . $existsDql . ')');
        }
    }
}
