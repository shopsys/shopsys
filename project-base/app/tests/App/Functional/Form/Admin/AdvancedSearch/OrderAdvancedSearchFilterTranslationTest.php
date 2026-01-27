<?php

declare(strict_types=1);

namespace Tests\App\Functional\Form\Admin\AdvancedSearch;

use Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchTranslationNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\OrderAdvancedSearchConfig;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\OrderAdvancedSearchFilterTranslation;
use Tests\App\Test\FunctionalTestCase;

class OrderAdvancedSearchFilterTranslationTest extends FunctionalTestCase
{
    /**
     * @inject
     */
    private OrderAdvancedSearchConfig $advancedSearchConfig;

    /**
     * @inject
     */
    private OrderAdvancedSearchFilterTranslation $orderAdvancedSearchFilterTranslation;

    public function testTranslateFilterName(): void
    {
        foreach ($this->advancedSearchConfig->getAllFilters() as $filter) {
            $this->assertNotEmpty(
                $this->orderAdvancedSearchFilterTranslation->translateFilterName($filter->getName()),
            );
        }
    }

    public function testTranslateFilterNameNotFoundException(): void
    {
        $advancedSearchTranslator = new OrderAdvancedSearchFilterTranslation();

        $this->expectException(AdvancedSearchTranslationNotFoundException::class);
        $advancedSearchTranslator->translateFilterName('nonexistingFilterName');
    }
}
