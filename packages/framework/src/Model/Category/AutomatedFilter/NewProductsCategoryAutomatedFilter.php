<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category\AutomatedFilter;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;

class NewProductsCategoryAutomatedFilter implements CategoryAutomatedFilterInterface
{
    protected const int MAX_PRODUCT_AGE_IN_DAYS = 30;
    protected const string DATABASE_VALUE = 'newProducts';

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return t('Display new products only');
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
        return $filterQuery->filterBySellingFrom(new DateTimeImmutable('-' . self::MAX_PRODUCT_AGE_IN_DAYS . ' days'));
    }

    /**
     * {@inheritdoc}
     */
    public function getNote(): ?string
    {
        return t('Only products with "Selling start date" not older than %count% days are displayed', ['%count%' => self::MAX_PRODUCT_AGE_IN_DAYS]);
    }
}
