<?php

declare(strict_types=1);

namespace Tests\ProductFeed\ZboziBundle\Unit;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryDataFactory;
use Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryDownloader;

class ZboziCategoryDownloaderTest extends TestCase
{
    private string $categoriesJsonFile;

    #[Override]
    protected function setUp(): void
    {
        $categoriesJsonFile = tempnam(sys_get_temp_dir(), 'zbozi-categories');
        self::assertIsString($categoriesJsonFile);

        $this->categoriesJsonFile = $categoriesJsonFile;
    }

    #[Override]
    protected function tearDown(): void
    {
        unlink($this->categoriesJsonFile);
    }

    public function testGetZboziCategoriesExtractsLeafCategoriesWithCategoryText(): void
    {
        file_put_contents($this->categoriesJsonFile, json_encode([
            [
                'name' => 'Foto',
                'children' => [
                    [
                        'name' => 'Foto doplnky',
                        'children' => [
                            [
                                'id' => 10,
                                'name' => 'Blesky',
                                'categoryText' => 'Foto | Foto doplnky | Blesky',
                            ],
                        ],
                    ],
                    [
                        'id' => 11,
                        'name' => 'Objektivy',
                        'categoryText' => 'Foto | Objektivy',
                    ],
                ],
            ],
            [
                'name' => 'Kategorie bez categoryText',
            ],
        ], JSON_THROW_ON_ERROR));
        $zboziCategoryDownloader = new ZboziCategoryDownloader(
            ['cs' => $this->categoriesJsonFile],
            new ZboziCategoryDataFactory(),
        );

        $zboziCategoriesData = $zboziCategoryDownloader->getZboziCategories('cs');

        self::assertCount(2, $zboziCategoriesData);
        self::assertSame('Blesky', $zboziCategoriesData[10]->name);
        self::assertSame('Foto | Foto doplnky | Blesky', $zboziCategoriesData[10]->fullName);
        self::assertSame('cs', $zboziCategoriesData[10]->locale);
        self::assertSame('Objektivy', $zboziCategoriesData[11]->name);
        self::assertSame('Foto | Objektivy', $zboziCategoriesData[11]->fullName);
    }
}
