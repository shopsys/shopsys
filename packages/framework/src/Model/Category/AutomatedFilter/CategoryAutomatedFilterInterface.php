<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category\AutomatedFilter;

use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;

interface CategoryAutomatedFilterInterface
{
    /**
     * Used for the choice label in the category form
     *
     * @return string
     */
    public function getLabel(): string;

    /**
     * Used for the additional choice info displayed as an icon tooltip in the category form. When set to null, no icon is displayed.
     *
     * @return string|null
     */
    public function getNote(): ?string;

    /**
     * Used for the value that is stored in the database when category is filtered by this filter
     *
     * @return string
     */
    public function getDatabaseValue(): string;

    /**
     * Modifies the filter query based on the filter
     *
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery $filterQuery
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function applyFilter(FilterQuery $filterQuery): FilterQuery;
}
