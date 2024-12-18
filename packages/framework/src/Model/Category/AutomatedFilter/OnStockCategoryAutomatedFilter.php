<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category\AutomatedFilter;

use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;

class OnStockCategoryAutomatedFilter implements CategoryAutomatedFilterInterface
{
    protected const string DATABASE_VALUE = 'onStock';

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return t('Display on stock products only');
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabaseValue(): string
    {
        return self::DATABASE_VALUE;
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilter(FilterQuery $filterQuery): FilterQuery
    {
        return $filterQuery->filterOnlyInStock();
    }

    /**
     * {@inheritdoc}
     */
    public function getNote(): ?string
    {
        return null;
    }
}
