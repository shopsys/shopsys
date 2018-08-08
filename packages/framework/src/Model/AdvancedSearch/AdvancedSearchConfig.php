<?php

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

class AdvancedSearchConfig
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface[]
     */
    private $filters;

    public function __construct()
    {
        $this->filters = [];
    }

    public function registerFilter(AdvancedSearchFilterInterface $filter): void
    {
        if (array_key_exists($filter->getName(), $this->filters)) {
            $message = 'Filter "' . $filter->getName() . '" already exists.';
            throw new \Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchFilterAlreadyExistsException($message);
        }

        $this->filters[$filter->getName()] = $filter;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface[]
     */
    public function getAllFilters(): array
    {
        return $this->filters;
    }

    public function getFilter(string $filterName): \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface
    {
        if (!array_key_exists($filterName, $this->filters)) {
            $message = 'Filter "' . $filterName . '" not found.';
            throw new \Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchFilterNotFoundException($message);
        }

        return $this->filters[$filterName];
    }
}
