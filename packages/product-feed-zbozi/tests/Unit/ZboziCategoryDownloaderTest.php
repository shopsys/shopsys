<?php

declare(strict_types=1);

namespace Tests\ProductFeed\ZboziBundle\Unit;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryDataFactory;
use Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryDownloader;

class ZboziCategoryDownloaderTest extends TestCase
{
    private string $categoriesCsvFile;

    #[Override]
    protected function setUp(): void
    {
        $categoriesCsvFile = tempnam(sys_get_temp_dir(), 'zbozi-categories');
        self::assertIsString($categoriesCsvFile);

        $this->categoriesCsvFile = $categoriesCsvFile;
    }

    #[Override]
    protected function tearDown(): void
    {
        unlink($this->categoriesCsvFile);
    }

    public function testGetZboziCategoriesReadsFlatCsvAndConvertsEncoding(): void
    {
        $csvUtf8 = "id kategorie;Název kategorie;Celá cesta\r\n"
            . "10;Blesky;Foto | Foto doplňky | Blesky\r\n"
            . "11;Objektivy;Foto | Objektivy\r\n"
            . ";Kategorie bez ID;Foto | Kategorie bez ID\r\n"
            . "broken-row-without-enough-columns\r\n";
        $csvWindows1250 = mb_convert_encoding($csvUtf8, 'Windows-1250', 'UTF-8');
        self::assertIsString($csvWindows1250);
        file_put_contents($this->categoriesCsvFile, $csvWindows1250);

        $zboziCategoryDownloader = new ZboziCategoryDownloader(
            ['cs' => $this->categoriesCsvFile],
            new ZboziCategoryDataFactory(),
        );

        $zboziCategoriesData = $zboziCategoryDownloader->getZboziCategories('cs');

        self::assertCount(2, $zboziCategoriesData);
        self::assertSame('Blesky', $zboziCategoriesData[10]->name);
        self::assertSame('Foto | Foto doplňky | Blesky', $zboziCategoriesData[10]->fullName);
        self::assertSame('cs', $zboziCategoriesData[10]->locale);
        self::assertSame('Objektivy', $zboziCategoriesData[11]->name);
        self::assertSame('Foto | Objektivy', $zboziCategoriesData[11]->fullName);
    }
}
