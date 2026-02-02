<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category\AutomatedFilter;

use Override;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;

class OnStockCategoryAutomatedFilter implements CategoryAutomatedFilterInterface
{
    public const string DATABASE_VALUE = 'onStock';

    #[Override]
    public function getLabel(): string
    {
        return t('Display on stock products only');
    }

    #[Override]
    public function getDatabaseValue(): string
    {
        return self::DATABASE_VALUE;
    }

    #[Override]
    public function applyFilter(FilterQuery $filterQuery): FilterQuery
    {
        return $filterQuery->filterOnlyInStock();
    }

    #[Override]
    public function getNote(): ?string
    {
        return null;
    }
}
