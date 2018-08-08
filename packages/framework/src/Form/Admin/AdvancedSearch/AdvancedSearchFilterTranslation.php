<?php

namespace Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch;

class AdvancedSearchFilterTranslation
{
    /**
     * @var string[]
     */
    private $filtersTranslationsByFilterName;

    public function __construct()
    {
        $this->filtersTranslationsByFilterName = [];
    }

    public function addFilterTranslation(string $filterName, string $filterTranslation): void
    {
        $this->filtersTranslationsByFilterName[$filterName] = $filterTranslation;
    }

    public function translateFilterName(string $filterName): string
    {
        if (array_key_exists($filterName, $this->filtersTranslationsByFilterName)) {
            return $this->filtersTranslationsByFilterName[$filterName];
        }

        $message = 'Filter "' . $filterName . '" translation not found.';
        throw new \Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchTranslationNotFoundException($message);
    }
}
