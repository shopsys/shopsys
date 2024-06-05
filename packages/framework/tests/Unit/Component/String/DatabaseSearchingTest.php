<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\String;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;

class DatabaseSearchingTest extends TestCase
{
    public static function searchTextProvider()
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
     * @param mixed $searchText
     * @param mixed $querySearchStringQuery
     */
    #[DataProvider('searchTextProvider')]
    public function testSafeFilename($searchText, $querySearchStringQuery)
    {
        $this->assertSame($querySearchStringQuery, DatabaseSearching::getLikeSearchString($searchText));
    }
}
