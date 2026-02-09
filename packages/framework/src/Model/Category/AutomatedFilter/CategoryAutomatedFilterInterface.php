<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category\AutomatedFilter;

use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;

interface CategoryAutomatedFilterInterface
{
    /**
     * Used for the choice label in the category form
     */
    public function getLabel(): string;

    /**
     * Used for the additional choice info displayed as an icon tooltip in the category form. When set to null, no icon is displayed.
     */
    public function getNote(): ?string;

    /**
     * Used for the value that is stored in the database when category is filtered by this filter
     */
    public function getDatabaseValue(): string;

    /**
     * Modifies the filter query based on the filter
     */
    public function applyFilter(FilterQuery $filterQuery): FilterQuery;
}
