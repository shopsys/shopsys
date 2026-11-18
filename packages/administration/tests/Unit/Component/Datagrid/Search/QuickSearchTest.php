<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Search;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearch;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\SearchedPath;

final class QuickSearchTest extends TestCase
{
    private const string UUID = 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11';

    public function testNothingIsNarrowedWithoutTerm(): void
    {
        $quickSearch = new QuickSearch('Product_search', null, [$this->createTextPath('name', 'Name')]);

        $this->assertFalse($quickSearch->isActive());
        $this->assertNull($quickSearch->createCondition());
    }

    public function testTermIsSearchedInEveryPathByDisjunction(): void
    {
        $quickSearch = new QuickSearch('Product_search', 'hrnek', [
            $this->createTextPath('name', 'Name'),
            $this->createTextPath('catnum', 'Catalogue number'),
            new SearchedPath('brand.name', 'Brand', null),
        ]);

        $this->assertTrue($quickSearch->isActive());
        $this->assertEquals(
            Condition::orX(
                Condition::contains('name', 'hrnek'),
                Condition::contains('catnum', 'hrnek'),
                Condition::contains('brand.name', 'hrnek'),
            ),
            $quickSearch->createCondition(),
        );
    }

    /**
     * Searched in the canonical form, as the database refuses the prefix or the braces the term may carry.
     */
    public function testUuidPathEqualsTermThatIsUuid(): void
    {
        $quickSearch = new QuickSearch('Product_search', 'urn:uuid:' . strtoupper(self::UUID), [
            $this->createTextPath('name', 'Name'),
            $this->createUuidPath(),
        ]);

        $this->assertEquals(
            Condition::orX(Condition::contains('name', 'urn:uuid:' . strtoupper(self::UUID)), Condition::equals('uuid', self::UUID)),
            $quickSearch->createCondition(),
        );
    }

    /**
     * The database refuses to compare a UUID with a text that is none.
     */
    public function testUuidPathIsSkippedWhenTermIsNoUuid(): void
    {
        $quickSearch = new QuickSearch('Product_search', substr(self::UUID, 0, 8), [
            $this->createTextPath('name', 'Name'),
            $this->createUuidPath(),
        ]);

        $this->assertEquals(Condition::orX(Condition::contains('name', substr(self::UUID, 0, 8))), $quickSearch->createCondition());
    }

    public function testNothingIsFoundWhenNoPathIsLeft(): void
    {
        $quickSearch = new QuickSearch('Product_search', 'hrnek', [$this->createUuidPath()]);

        $this->assertEquals(Condition::orX(), $quickSearch->createCondition());
    }

    public function testEveryLabelIsListedOnce(): void
    {
        $quickSearch = new QuickSearch('Product_search', null, [
            $this->createTextPath('productName', 'Product name or catalog number'),
            $this->createTextPath('catnum', 'Product name or catalog number'),
            $this->createTextPath('lastName', 'Reviewer name'),
            $this->createUuidPath(),
        ]);

        $this->assertSame(['Product name or catalog number', 'Reviewer name', 'UUID'], $quickSearch->getLabels());
    }

    private function createTextPath(string $path, string $label): SearchedPath
    {
        return new SearchedPath($path, $label, PathValueTypeEnum::STRING);
    }

    private function createUuidPath(): SearchedPath
    {
        return new SearchedPath('uuid', 'UUID', PathValueTypeEnum::GUID);
    }
}
