<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AbstractAdvancedSearchFilter;
use Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\ProductAdvancedSearchFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductDomain;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormTypeInterface;

class ProductCalculatedSellingDeniedFilter extends AbstractAdvancedSearchFilter
{
    public const string NAME = 'productCalculatedSellingDenied';

    public function __construct(
        DatabaseSearchingHelper $databaseSearchingHelper,
        protected readonly EntityManagerInterface $em,
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
        return t('Excluded from sale');
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
        return HiddenType::class;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchRuleData[] $rulesData
     */
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, array $rulesData): void
    {
        $existsDql = $this->em->createQueryBuilder()
            ->select('1')
            ->from(ProductDomain::class, 'pds')
            ->where('pds.product = p.id')
            ->andWhere('pds.calculatedSellingDenied = true')
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

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getEntityType(): string
    {
        return ProductAdvancedSearchFacade::getEntityType();
    }
}
