<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\AdvancedSearch;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AbstractAdvancedSearchFacade;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchQueryBuilderExtender;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\RuleFormViewDataFactory;
use Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\ProductNameFilter;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminFacade;

class ProductAdvancedSearchFacade extends AbstractAdvancedSearchFacade
{
    public function __construct(
        ProductAdvancedSearchFormFactory $advancedSearchFormFactory,
        RuleFormViewDataFactory $ruleFormViewDataFactory,
        protected readonly AdvancedSearchQueryBuilderExtender $advancedSearchQueryBuilderExtender,
        protected readonly ProductListAdminFacade $productListAdminFacade,
    ) {
        parent::__construct($advancedSearchFormFactory, $ruleFormViewDataFactory);
    }

    public function getQueryBuilderByAdvancedSearchData(array $advancedSearchData): QueryBuilder
    {
        $queryBuilder = $this->productListAdminFacade->getProductListQueryBuilder();
        $this->advancedSearchQueryBuilderExtender->extendByAdvancedSearchData($queryBuilder, $advancedSearchData, static::getEntityType());

        return $queryBuilder;
    }

    /**
     * @return string
     */
    #[Override]
    protected function getDefaultFilterName(): string
    {
        return ProductNameFilter::NAME;
    }

    /**
     * @return string
     */
    #[Override]
    public static function getEntityType(): string
    {
        return 'product';
    }
}
