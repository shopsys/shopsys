<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormTypeInterface;

class OrderWithdrawalRequestFilter implements AdvancedSearchFilterInterface
{
    public const string NAME = 'orderWithdrawalRequest';

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
            self::OPERATOR_EXISTS,
            self::OPERATOR_DOES_NOT_EXIST,
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormType(): FormTypeInterface|string
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
        foreach ($rulesData as $ruleData) {
            if ($ruleData->operator === self::OPERATOR_EXISTS) {
                $queryBuilder->andWhere('o.withdrawalRequest IS NOT NULL');
            }

            if ($ruleData->operator === self::OPERATOR_DOES_NOT_EXIST) {
                $queryBuilder->andWhere('o.withdrawalRequest IS NULL');
            }
        }
    }
}
