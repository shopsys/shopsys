<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\DatagridConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class DatagridConfigTest extends TestCase
{
    /**
     * A form name allows only letters, digits, underscores, hyphens and colons; the ID of a datagrid is free text.
     */
    public function testFormNamesAreSafeForAnyDatagridId(): void
    {
        $config = new DatagridConfig('Product review', new CapturingAdapter($this->createStub(DataSourceInterface::class)), [], new FilterCollection());

        $this->assertSame('Product_review_search', $config->createFormName('_search'));
    }
}
