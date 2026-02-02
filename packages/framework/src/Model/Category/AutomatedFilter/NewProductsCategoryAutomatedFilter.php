<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category\AutomatedFilter;

use Override;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;

class NewProductsCategoryAutomatedFilter implements CategoryAutomatedFilterInterface
{
    protected const int MAX_PRODUCT_AGE_IN_DAYS = 30;
    public const string DATABASE_VALUE = 'newProducts';

    public function __construct(
        protected readonly ClockInterface $clock,
    ) {
    }

    #[Override]
    public function getLabel(): string
    {
        return t('Display new products only');
    }

    #[Override]
    public function getDatabaseValue(): string
    {
        return self::DATABASE_VALUE;
    }

    #[Override]
    public function applyFilter(FilterQuery $filterQuery): FilterQuery
    {
        return $filterQuery->filterBySellingFrom($this->clock->now()->modify('-' . self::MAX_PRODUCT_AGE_IN_DAYS . ' days'));
    }

    #[Override]
    public function getNote(): ?string
    {
        return t('Only products with "Selling start date" not older than %count% days are displayed', ['%count%' => self::MAX_PRODUCT_AGE_IN_DAYS]);
    }
}
