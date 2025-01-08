<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\String;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;

class DatabaseSearchingTest extends TestCase
{
    private DatabaseSearchingHelper $databaseSearchingHelper;

    protected function setUp(): void
    {
        $this->databaseSearchingHelper = new DatabaseSearchingHelper();

        parent::setUp();
    }

    /**
     * @return array
     */
    public static function searchTextProvider(): array
    {
        return [
            ['searchText' => 'foo bar', 'querySearchStringQuery' => 'foo bar'],
            ['searchText' => 'FooBar', 'querySearchStringQuery' => 'FooBar'],
            ['searchText' => 'foo*bar', 'querySearchStringQuery' => 'foo%bar'],
            ['searchText' => 'foo%', 'querySearchStringQuery' => 'foo\%'],
            ['searchText' => 'fo?o%', 'querySearchStringQuery' => 'fo_o\%'],
            ['searchText' => '_foo', 'querySearchStringQuery' => '\_foo'],
        ];
    }

    /**
     * @param string $searchText
     * @param string $querySearchStringQuery
     */
    #[DataProvider('searchTextProvider')]
    public function testSafeFilename(string $searchText, string $querySearchStringQuery): void
    {
        $this->assertSame($querySearchStringQuery, $this->databaseSearchingHelper->getLikeSearchString($searchText));
    }
}
