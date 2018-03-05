<?php

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ProductBrandFilter implements AdvancedSearchFilterInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade
     */
    private $brandFacade;

    public function __construct(BrandFacade $brandFacade)
    {
        $this->brandFacade = $brandFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedOperators()
    {
        return [
            self::OPERATOR_IS,
            self::OPERATOR_IS_NOT,
            self::OPERATOR_NOT_SET,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'productBrand';
    }

    /**
     * {@inheritdoc}
     */
    public function getValueFormOptions()
    {
        return [
            'expanded' => false,
            'multiple' => false,
            'choices' => $this->brandFacade->getAll(),
            'choice_label' => 'name',
            'choice_value' => 'id',
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
    public function extendQueryBuilder(QueryBuilder $queryBuilder, $rulesData)
    {
        $isNotBrand = [];

        foreach ($rulesData as $index => $ruleData) {
            if ($ruleData->operator === self::OPERATOR_NOT_SET) {
                $queryBuilder->andWhere('p.brand IS NULL');
            } elseif ($ruleData->operator === self::OPERATOR_IS) {
                $tableAlias = 'b' . $index;
                $brandParameter = 'brand' . $index;
                $queryBuilder->join('p.brand', $tableAlias, Join::WITH, $tableAlias . '.id = :' . $brandParameter);
                $queryBuilder->setParameter($brandParameter, $ruleData->value);
            } elseif ($ruleData->operator === self::OPERATOR_IS_NOT) {
                $isNotBrand[] = $ruleData->value;
            }
        }

        if (count($isNotBrand) > 0) {
            $subQuery = 'SELECT brand_p.id FROM ' . Product::class . ' brand_p
                JOIN brand_p.brand _f WITH _f.id IN (:isNotBrand)';
            $queryBuilder->andWhere('p.id NOT IN (' . $subQuery . ')');
            $queryBuilder->setParameter('isNotBrand', $isNotBrand);
        }
    }
}
