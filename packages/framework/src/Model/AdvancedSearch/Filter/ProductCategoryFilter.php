<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter;

use BadMethodCallException;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Localization\Localization as LocalizationAlias;
use Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ProductCategoryFilter implements AdvancedSearchFilterInterface
{
    public const NAME = 'productCategory';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    protected $categoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain|null
     * @deprecated This will be removed in next major version
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization|null
     */
    protected $localization;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain|null $domain
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization|null $localization
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        ?Domain $domain = null,
        ?LocalizationAlias $localization = null
    ) {
        $this->categoryFacade = $categoryFacade;
        $this->domain = $domain;
        $this->localization = $localization;
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setLocalization(Localization $localization): void
    {
        if ($this->localization !== null && $this->localization !== $localization) {
            throw new BadMethodCallException(sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__));
        }
        if ($this->localization === null) {
            @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.', __METHOD__), E_USER_DEPRECATED);
            $this->localization = $localization;
        }
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
        if (count($isCategory) + count($isNotCategory) > 0) {
            $subQuery = 'SELECT IDENTITY(%s.product) FROM ' . ProductCategoryDomain::class . ' %1$s WHERE %1$s.category IN (:%s)';

            if (count($isCategory) > 0) {
                $queryBuilder->andWhere($queryBuilder->expr()->in('p.id', sprintf($subQuery, 'pcd_is', 'isCategory')));
                $queryBuilder->setParameter('isCategory', $isCategory);
            }
            if (count($isNotCategory) > 0) {
                $queryBuilder->andWhere($queryBuilder->expr()->notIn('p.id', sprintf($subQuery, 'pcd_not', 'isNotCategory')));
                $queryBuilder->setParameter('isNotCategory', $isNotCategory);
            }
        }
    }
}
