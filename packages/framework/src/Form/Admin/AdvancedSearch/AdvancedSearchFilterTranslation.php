<?php

namespace Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch;

use Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchTranslationNotFoundException;

class AdvancedSearchFilterTranslation
{
    /**
     * @var string[]
     */
    protected $filtersTranslationsByFilterName;

    public function __construct()
    {
        $this->filtersTranslationsByFilterName = [];
    }

    /**
     * @param string $filterName
     * @param string $filterTranslation
     */
    public function addFilterTranslation($filterName, $filterTranslation)
    {
        $this->filtersTranslationsByFilterName[$filterName] = $filterTranslation;
    }

    /**
     * @param string $filterName
     * @return string
     */
    public function translateFilterName($filterName)
    {
        if (array_key_exists($filterName, $this->filtersTranslationsByFilterName)) {
            return $this->filtersTranslationsByFilterName[$filterName];
        }

        $message = 'Filter "' . $filterName . '" translation not found.';

        throw new AdvancedSearchTranslationNotFoundException($message);
    }
}
