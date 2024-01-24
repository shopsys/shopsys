<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductDomain;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ProductFlagFilter implements AdvancedSearchFilterInterface
{
    public const NAME = 'productFlag';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     */
    public function __construct(protected readonly FlagFacade $flagFacade)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedOperators()
    {
        return [
            self::OPERATOR_IS,
            self::OPERATOR_IS_NOT,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getValueFormType()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getValueFormOptions()
    {
        return [
            'expanded' => false,
            'multiple' => false,
            'choices' => $this->flagFacade->getAll(),
            'choice_label' => 'name',
            'choice_value' => 'id',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function extendQueryBuilder(QueryBuilder $queryBuilder, $rulesData)
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
