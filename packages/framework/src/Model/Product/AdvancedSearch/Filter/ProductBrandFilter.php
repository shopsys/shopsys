<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AbstractAdvancedSearchFilter;
use Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\ProductAdvancedSearchFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormTypeInterface;

class ProductBrandFilter extends AbstractAdvancedSearchFilter
{
    public const string NAME = 'productBrand';

    public function __construct(
        DatabaseSearchingHelper $databaseSearchingHelper,
        protected readonly BrandFacade $brandFacade,
    ) {
        parent::__construct($databaseSearchingHelper);
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
            self::OPERATOR_NOT_SET,
        ];
    }

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
    public function getValueFormOptions(): array
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
    #[Override]
    public function getValueFormType(): FormTypeInterface|string
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, array $rulesData): void
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

        if (count($isNotBrand) === 0) {
            return;
        }

        $subQuery = 'SELECT brand_p.id FROM ' . Product::class . ' brand_p
            JOIN brand_p.brand _f WITH _f.id IN (:isNotBrand)';
        $queryBuilder->andWhere('p.id NOT IN (' . $subQuery . ')');
        $queryBuilder->setParameter('isNotBrand', $isNotBrand);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getEntityType(): string
    {
        return ProductAdvancedSearchFacade::getEntityType();
    }
}
