<?php

declare(strict_types=1);

namespace Tests\ProductFeed\ZboziBundle\Unit;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryDataFactory;
use Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryDownloader;
use Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryDownloadFailedException;

final class ZboziCategoryDownloaderTest extends TestCase
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
        file_put_contents($this->categoriesCsvFile, $this->convertToWindows1250($csvUtf8));

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

    /**
     * @return iterable<string, array{csvContent: string}>
     */
    public static function getInvalidCsvContentDataProvider(): iterable
    {
        yield 'empty file' => [
            'csvContent' => '',
        ];

        yield 'header only' => [
            'csvContent' => "id kategorie;Název kategorie;Celá cesta\r\n",
        ];

        yield 'rows without numeric ID' => [
            'csvContent' => "id kategorie;Název kategorie;Celá cesta\r\n"
                . ";Kategorie bez ID;Foto | Kategorie bez ID\r\n"
                . "broken-row-without-enough-columns\r\n",
        ];
    }

    #[DataProvider('getInvalidCsvContentDataProvider')]
    public function testGetZboziCategoriesFailsWhenCsvDoesNotContainAnyCategory(string $csvContent): void
    {
        file_put_contents($this->categoriesCsvFile, $this->convertToWindows1250($csvContent));

        $zboziCategoryDownloader = new ZboziCategoryDownloader(
            ['cs' => $this->categoriesCsvFile],
            new ZboziCategoryDataFactory(),
        );

        $this->expectException(ZboziCategoryDownloadFailedException::class);
        $this->expectExceptionMessage('Downloaded Zbozi.cz categories CSV is empty or has invalid format.');

        $zboziCategoryDownloader->getZboziCategories('cs');
    }

    public function testGetZboziCategoriesFailsWhenCsvCannotBeOpened(): void
    {
        $zboziCategoryDownloader = new ZboziCategoryDownloader(
            ['cs' => $this->categoriesCsvFile . '-missing'],
            new ZboziCategoryDataFactory(),
        );

        $this->expectException(ZboziCategoryDownloadFailedException::class);
        $this->expectExceptionMessage('Unable to download Zbozi.cz categories CSV');

        $zboziCategoryDownloader->getZboziCategories('cs');
    }

    private function convertToWindows1250(string $content): string
    {
        $encodedContent = iconv('UTF-8', 'CP1250', $content);
        self::assertIsString($encodedContent);

        return $encodedContent;
    }
}
