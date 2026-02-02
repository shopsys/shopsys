<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductDomain;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormTypeInterface;

class ProductFlagFilter implements AdvancedSearchFilterInterface
{
    public const string NAME = 'productFlag';

    public function __construct(protected readonly FlagFacade $flagFacade)
    {
    }

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
    public function getValueFormType(): FormTypeInterface|string
    {
        return ChoiceType::class;
    }

    #[Override]
    public function getValueFormOptions(): array
    {
        return [
            'expanded' => false,
            'multiple' => false,
            'choices' => $this->flagFacade->getAll(),
            'choice_label' => 'name',
            'choice_value' => 'id',
        ];
    }

    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, array $rulesData): void
    {
        $isFlags = [];
        $isNotFlags = [];

        foreach ($rulesData as $ruleData) {
            if ($ruleData->operator === self::OPERATOR_IS) {
                $isFlags[] = $ruleData->value;
            } elseif ($ruleData->operator === self::OPERATOR_IS_NOT) {
                $isNotFlags[] = $ruleData->value;
            }
        }

        if (count($isFlags) + count($isNotFlags) === 0) {
            return;
        }

        if (count($isFlags) > 0) {
            $subQuery = 'SELECT IDENTITY(pdSub.product) FROM ' . ProductDomain::class . ' pdSub JOIN pdSub.flags AS fSub WHERE fSub.id IN (:isFlags)';
            $queryBuilder->andWhere($queryBuilder->expr()->in('p.id', $subQuery));
            $queryBuilder->setParameter('isFlags', $isFlags);
        }

        if (count($isNotFlags) === 0) {
            return;
        }

        $subQuery = 'SELECT IDENTITY(pdSubNot.product) FROM ' . ProductDomain::class . ' pdSubNot JOIN pdSubNot.flags AS fSubNot WHERE fSubNot.id IN (:isNotFlags)';
        $queryBuilder->andWhere($queryBuilder->expr()->notIn('p.id', $subQuery));
        $queryBuilder->setParameter('isNotFlags', $isNotFlags);
    }
}
