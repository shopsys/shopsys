<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\String;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;

class DatabaseSearchingTest extends TestCase
{
    /**
     * @return array<int, array<'querySearchStringQuery'|'searchText', string>>
     */
    public function searchTextProvider(): array
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
     * @dataProvider searchTextProvider
     * @param string $searchText
     * @param string $querySearchStringQuery
     */
    public function testSafeFilename(string $searchText, string $querySearchStringQuery): void
    {
        $this->assertSame($querySearchStringQuery, DatabaseSearching::getLikeSearchString($searchText));
    }
}
