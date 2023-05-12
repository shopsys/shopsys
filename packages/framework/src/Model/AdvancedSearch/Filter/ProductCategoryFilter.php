<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ProductCategoryFilter implements AdvancedSearchFilterInterface
{
    public const NAME = 'productCategory';

    protected ?Localization $localization = null;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        protected readonly CategoryFacade $categoryFacade,
        Localization $localization
    ) {
        $this->localization = $localization;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
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
            'choices' => $this->categoryFacade->getAllTranslated($this->localization->getAdminLocale()),
            'choice_label' => function (Category $category) {
                $padding = str_repeat("\u{00a0}", ($category->getLevel() - 1) * 2);

                return $padding . $category->getName();
            },
            'choice_value' => 'id',
            'attr' => ['class' => 'js-autocomplete-selectbox'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function extendQueryBuilder(QueryBuilder $queryBuilder, $rulesData)
    {
        $isCategory = [];
        $isNotCategory = [];

        foreach ($rulesData as $ruleData) {
            if ($ruleData->operator === self::OPERATOR_IS) {
                $isCategory[] = $ruleData->value;
            } elseif ($ruleData->operator === self::OPERATOR_IS_NOT) {
                $isNotCategory[] = $ruleData->value;
            }
        }

        if (count($isCategory) + count($isNotCategory) === 0) {
            return;
        }

        $subQuery = 'SELECT IDENTITY(%s.product) FROM ' . ProductCategoryDomain::class . ' %1$s WHERE %1$s.category IN (:%s)';

        if (count($isCategory) > 0) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('p.id', sprintf($subQuery, 'pcd_is', 'isCategory')));
            $queryBuilder->setParameter('isCategory', $isCategory);
        }

        if (count($isNotCategory) === 0) {
            return;
        }

        $queryBuilder->andWhere($queryBuilder->expr()->notIn('p.id', sprintf($subQuery, 'pcd_not', 'isNotCategory')));
        $queryBuilder->setParameter('isNotCategory', $isNotCategory);
    }
}
