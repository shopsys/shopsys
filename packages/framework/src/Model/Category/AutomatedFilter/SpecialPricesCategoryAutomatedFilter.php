<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category\AutomatedFilter;

use Override;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;

class SpecialPricesCategoryAutomatedFilter implements CategoryAutomatedFilterInterface
{
    public const string DATABASE_VALUE = 'specialPrices';

    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    #[Override]
    public function getLabel(): string
    {
        return t('Display products with special prices only');
    }

    #[Override]
    public function getNote(): ?string
    {
        return t('Only products with active special price from a price list be displayed');
    }

    #[Override]
    public function getDatabaseValue(): string
    {
        return self::DATABASE_VALUE;
    }

    #[Override]
    public function applyFilter(FilterQuery $filterQuery): FilterQuery
    {
        return $filterQuery->filterWithActiveSpecialPriceOnly($this->currentCustomerUser->getPricingGroup());
    }
}
