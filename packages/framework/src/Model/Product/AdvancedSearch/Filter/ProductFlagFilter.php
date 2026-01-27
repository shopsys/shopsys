<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AbstractAdvancedSearchFilter;
use Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\ProductAdvancedSearchFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductDomain;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormTypeInterface;

class ProductFlagFilter extends AbstractAdvancedSearchFilter
{
    public const string NAME = 'productFlag';

    public function __construct(
        DatabaseSearchingHelper $databaseSearchingHelper,
        protected readonly FlagFacade $flagFacade,
    ) {
        parent::__construct($databaseSearchingHelper);
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
    public function getLabel(): string
    {
        return t('Flag');
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

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getEntityType(): string
    {
        return ProductAdvancedSearchFacade::getEntityType();
    }
}
