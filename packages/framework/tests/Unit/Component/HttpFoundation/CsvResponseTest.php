<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\HttpFoundation;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\HttpFoundation\CsvResponse;

class CsvResponseTest extends TestCase
{
    /**
     * @var array<int, array<string, string>>
     */
    private const array EXPORT_DATA = [
        [
            'product_catnum' => '500A',
            'price' => '99.9',
        ],
    ];

    public function testProvidedCsvHeadersDefineTheColumnOrder(): void
    {
        $response = new CsvResponse(
            self::EXPORT_DATA,
            'export.csv',
            ['PRICE' => 'price', 'PRODUCT_CATNUM' => 'product_catnum'],
        );

        $this->assertSame("price,product_catnum\n99.9,500A\n", $response->getContent());
    }

    public function testCsvHeadersAreDerivedFromRowKeysWhenNotProvided(): void
    {
        $response = new CsvResponse(self::EXPORT_DATA, 'export.csv');

        $this->assertSame("product_catnum,price\n500A,99.9\n", $response->getContent());
    }

    public function testResponseHeaders(): void
    {
        $response = new CsvResponse(self::EXPORT_DATA, 'export.csv');

        $this->assertSame('text/csv', $response->headers->get('Content-Type'));
        $this->assertSame('attachment; filename="export.csv"', $response->headers->get('Content-Disposition'));
    }
}
