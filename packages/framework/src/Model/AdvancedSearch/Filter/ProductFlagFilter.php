<?php

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ProductFlagFilter implements AdvancedSearchFilterInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade
     */
    private $flagFacade;

    public function __construct(FlagFacade $flagFacade)
    {
        $this->flagFacade = $flagFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'productFlag';
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
        $isNotFlags = [];

        foreach ($rulesData as $index => $ruleData) {
            if ($ruleData->operator === self::OPERATOR_IS) {
                $tableAlias = 'f' . $index;
                $flagParameter = 'flag' . $index;
                $queryBuilder->join('p.flags', $tableAlias, Join::WITH, $tableAlias . '.id = :' . $flagParameter);
                $queryBuilder->setParameter($flagParameter, $ruleData->value);
            } elseif ($ruleData->operator === self::OPERATOR_IS_NOT) {
                $isNotFlags[] = $ruleData->value;
            }
        }

        if (count($isNotFlags) > 0) {
            $subQuery = 'SELECT flag_p.id FROM ' . Product::class . ' flag_p JOIN flag_p.flags _f WITH _f.id IN (:isNotFlags)';
            $queryBuilder->andWhere('p.id NOT IN (' . $subQuery . ')');
            $queryBuilder->setParameter('isNotFlags', $isNotFlags);
        }
    }
}
