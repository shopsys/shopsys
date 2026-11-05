<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Search;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearch;

final class QuickSearchTest extends TestCase
{
    public function testNothingIsNarrowedWithoutTerm(): void
    {
        $quickSearch = new QuickSearch('Product_search', null, ['name' => 'Name']);

        $this->assertFalse($quickSearch->isActive());
        $this->assertNull($quickSearch->createCondition());
    }

    public function testTermIsSearchedInEveryFieldByDisjunction(): void
    {
        $quickSearch = new QuickSearch('Product_search', 'hrnek', [
            'name' => 'Name',
            'catnum' => 'Catalogue number',
            'brand.name' => 'Brand',
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
     * A field combining several properties is searched in each of them, but named in the placeholder once.
     */
    public function testEveryFieldIsLabelledOnce(): void
    {
        $quickSearch = new QuickSearch('Product_search', null, [
            'productName' => 'Product',
            'catnum' => 'Product',
            'lastName' => 'Reviewer',
        ]);

        $this->assertSame(['Product', 'Reviewer'], $quickSearch->getLabels());
    }
}
