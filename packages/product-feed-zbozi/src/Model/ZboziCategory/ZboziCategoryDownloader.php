<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory;

use JsonException;

class ZboziCategoryDownloader
{
    /**
     * @param array<string, string> $feedUrlsByLocale
     */
    public function __construct(
        protected readonly array $feedUrlsByLocale,
        protected readonly ZboziCategoryDataFactory $zboziCategoryDataFactory,
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
     * @return \Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryData[]
     */
    public function getZboziCategories(string $locale): array
    {
        $url = $this->feedUrlsByLocale[$locale];
        $zboziCategoryData = $this->loadJson($url);

        return $this->convertToZboziCategoriesData($zboziCategoryData, $locale);
    }

    /**
     * @return array<int, mixed>
     */
    protected function loadJson(string $url): array
    {
        $json = file_get_contents($url);

        if ($json === false) {
            throw new ZboziCategoryDownloadFailedException('Unable to download Zbozi.cz categories JSON.');
        }

        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new ZboziCategoryDownloadFailedException('Unable to parse Zbozi.cz categories JSON.');
        }

        return is_array($data) ? $data : [];
    }

    /**
     * @param array<int, mixed> $zboziCategoryData
     * @return \Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryData[]
     */
    protected function convertToZboziCategoriesData(array $zboziCategoryData, string $locale): array
    {
        $zboziCategoriesData = [];
        $this->collectZboziCategoriesData($zboziCategoryData, $locale, $zboziCategoriesData);

        return $zboziCategoriesData;
    }

    /**
     * @param array<int, mixed> $categoriesData
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryData[] $zboziCategoriesData
     */
    protected function collectZboziCategoriesData(
        array $categoriesData,
        string $locale,
        array &$zboziCategoriesData,
    ): void {
        foreach ($categoriesData as $categoryData) {
            if (!is_array($categoryData)) {
                continue;
            }

            if (isset($categoryData['id'], $categoryData['categoryText'])) {
                $zboziCategoryData = $this->zboziCategoryDataFactory->create($locale);
                $zboziCategoryData->zboziId = (int)$categoryData['id'];
                $zboziCategoryData->name = isset($categoryData['name']) ? (string)$categoryData['name'] : null;
                $zboziCategoryData->fullName = (string)$categoryData['categoryText'];

                $zboziCategoriesData[$zboziCategoryData->zboziId] = $zboziCategoryData;
            }

            if (isset($categoryData['children']) && is_array($categoryData['children'])) {
                $this->collectZboziCategoriesData($categoryData['children'], $locale, $zboziCategoriesData);
            }
        }
    }
}
