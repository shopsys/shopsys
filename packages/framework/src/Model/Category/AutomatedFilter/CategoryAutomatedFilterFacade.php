<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category\AutomatedFilter;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Traversable;

class CategoryAutomatedFilterFacade
{
    /**
     * @param \Traversable<int, \Shopsys\FrameworkBundle\Model\Category\AutomatedFilter\CategoryAutomatedFilterInterface> $categoryAutomatedFilters
     */
    public function __construct(
        protected readonly Traversable $categoryAutomatedFilters,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function getAllValuesIndexedByLabel(): array
    {
        $valuesIndexedByLabel = [];

        foreach ($this->categoryAutomatedFilters as $categoryAutomatedFilter) {
            $valuesIndexedByLabel[$categoryAutomatedFilter->getLabel()] = $categoryAutomatedFilter->getDatabaseValue();
        }

        return $valuesIndexedByLabel;
    }

    /**
     * @return array<string, string>
     */
    public function getNotesIndexedByValue(): array
    {
        $notesIndexedByValue = [];

        foreach ($this->categoryAutomatedFilters as $categoryAutomatedFilter) {
            $notesIndexedByValue[$categoryAutomatedFilter->getDatabaseValue()] = $categoryAutomatedFilter->getNote();
        }

        return $notesIndexedByValue;
    }

    public function applyFiltersByCategory(FilterQuery $filterQuery, Category $category): FilterQuery
    {
        $filterQuery = $filterQuery->filterByCategory($category->getId());

        foreach ($this->getByCategory($category) as $automatedFilter) {
            $filterQuery = $automatedFilter->applyFilter($filterQuery);
        }

        return $filterQuery;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\AutomatedFilter\CategoryAutomatedFilterInterface[]
     */
    protected function getByCategory(Category $category): array
    {
        $categoryAutomatedFilters = [];

        foreach ($this->categoryAutomatedFilters as $categoryAutomatedFilter) {
            if ($category->isUsingAutomatedFilter($categoryAutomatedFilter)) {
                $categoryAutomatedFilters[] = $categoryAutomatedFilter;
            }
        }

        return $categoryAutomatedFilters;
    }
}
