<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Search;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearch;
use Symfony\Component\Form\FormInterface;

final class QuickSearchTest extends TestCase
{
    public function testNothingIsNarrowedWithoutTerm(): void
    {
        $quickSearch = new QuickSearch($this->createStub(FormInterface::class), null, ['name' => 'Name']);

        $this->assertNull($quickSearch->createCondition());
    }

    public function testTermIsSearchedInEveryFieldByDisjunction(): void
    {
        $quickSearch = new QuickSearch($this->createStub(FormInterface::class), 'hrnek', [
            'name' => 'Name',
            'catnum' => 'Catalogue number',
            'brand.name' => 'Brand',
        ]);

        $this->assertEquals(
            Condition::orX(
                Condition::contains('name', 'hrnek'),
                Condition::contains('catnum', 'hrnek'),
                Condition::contains('brand.name', 'hrnek'),
            ),
            $quickSearch->createCondition(),
        );
    }
}
