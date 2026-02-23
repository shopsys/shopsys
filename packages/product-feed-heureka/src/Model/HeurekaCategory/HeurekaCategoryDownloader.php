<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory;

use Exception;
use SimpleXMLElement;

class HeurekaCategoryDownloader
{
    /**
     * @param array<string, string> $feedUrlsByLocale
     */
    public function __construct(
        protected readonly array $feedUrlsByLocale,
        protected readonly HeurekaCategoryDataFactory $heurekaCategoryDataFactory,
    ) {
    }

    /**
     * @return string[]
     */
    public function getSupportedLocales(): array
    {
        return array_keys($this->feedUrlsByLocale);
    }

    /**
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryData[]
     */
    public function getHeurekaCategories(string $locale): array
    {
        $url = $this->feedUrlsByLocale[$locale];
        $xmlCategoryDataObjects = $this->loadXml($url)->xpath('/HEUREKA//CATEGORY[CATEGORY_FULLNAME]');

        return $this->convertToHeurekaCategoriesData($xmlCategoryDataObjects, $locale);
    }

    protected function loadXml(string $url): SimpleXMLElement
    {
        try {
            return new SimpleXMLElement($url, LIBXML_NOERROR | LIBXML_NOWARNING, true);
        } catch (Exception $e) {
            throw new HeurekaCategoryDownloadFailedException($e);
        }
    }

    /**
     * @param \SimpleXMLElement[] $xmlCategoryDataObjects
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryData[]
     */
    protected function convertToHeurekaCategoriesData(array $xmlCategoryDataObjects, string $locale): array
    {
        $heurekaCategoriesData = [];

        foreach ($xmlCategoryDataObjects as $xmlCategoryDataObject) {
            $categoryId = (int)$xmlCategoryDataObject->CATEGORY_ID;

            $heurekaCategoryData = $this->heurekaCategoryDataFactory->create($locale);
            $heurekaCategoryData->heurekaId = $categoryId;
            $heurekaCategoryData->name = (string)$xmlCategoryDataObject->CATEGORY_NAME;
            $heurekaCategoryData->fullName = (string)$xmlCategoryDataObject->CATEGORY_FULLNAME;

            $heurekaCategoriesData[$categoryId] = $heurekaCategoryData;
        }

        return $heurekaCategoriesData;
    }
}
