<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory;

class ZboziCategoryDownloader
{
    protected const string SOURCE_ENCODING = 'Windows-1250';
    protected const string CSV_DELIMITER = ';';

    protected const int CELL_INDEX_ID = 0;
    protected const int CELL_INDEX_NAME = 1;
    protected const int CELL_INDEX_FULL_NAME = 2;

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
        $stream = @fopen($url, 'rb');

        if ($stream === false) {
            throw new ZboziCategoryDownloadFailedException('Unable to download Zbozi.cz categories CSV.');
        }

        try {
            return $this->readZboziCategoriesFromCsvStream($stream, $locale);
        } finally {
            fclose($stream);
        }
    }

    /**
     * @param resource $stream
     * @return \Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryData[]
     */
    protected function readZboziCategoriesFromCsvStream($stream, string $locale): array
    {
        $zboziCategoriesData = [];

        while (($row = $this->readUtf8CsvRow($stream)) !== null) {
            $id = $row[self::CELL_INDEX_ID] ?? '';

            if (!ctype_digit($id)) {
                continue;
            }

            $zboziId = (int)$id;

            $zboziCategoryData = $this->zboziCategoryDataFactory->create($locale);
            $zboziCategoryData->zboziId = $zboziId;
            $zboziCategoryData->name = $row[self::CELL_INDEX_NAME] ?? '';
            $zboziCategoryData->fullName = $row[self::CELL_INDEX_FULL_NAME] ?? '';

            $zboziCategoriesData[$zboziId] = $zboziCategoryData;
        }

        return $zboziCategoriesData;
    }

    /**
     * @param resource $stream
     * @return string[]|null
     */
    protected function readUtf8CsvRow($stream): ?array
    {
        $row = fgetcsv($stream, separator: self::CSV_DELIMITER, escape: '');

        if ($row === false) {
            return null;
        }

        return array_map(
            static fn (?string $cell): string => $cell === null
                ? ''
                : (string)mb_convert_encoding($cell, 'UTF-8', self::SOURCE_ENCODING),
            $row,
        );
    }
}
