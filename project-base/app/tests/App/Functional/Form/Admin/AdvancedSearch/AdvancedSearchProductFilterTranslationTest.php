<?php

declare(strict_types=1);

namespace Tests\App\Functional\Form\Admin\AdvancedSearch;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchProductFilterTranslation;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchTranslationNotFoundException;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\ProductAdvancedSearchConfig;
use Tests\App\Test\FunctionalTestCase;

class AdvancedSearchProductFilterTranslationTest extends FunctionalTestCase
{
    /**
     * @inject
     */
    private ProductAdvancedSearchConfig $advancedSearchConfig;

    /**
     * @inject
     */
    private AdvancedSearchProductFilterTranslation $advancedSearchProductFilterTranslation;

    public function testTranslateFilterName()
    {
        foreach ($this->advancedSearchConfig->getAllFilters() as $filter) {
            $this->assertNotEmpty(
                $this->advancedSearchProductFilterTranslation->translateFilterName($filter->getName()),
            );
        }
    }

    public function testTranslateFilterNameNotFoundException()
    {
        $advancedSearchTranslator = new AdvancedSearchProductFilterTranslation();

        $this->expectException(AdvancedSearchTranslationNotFoundException::class);
        $advancedSearchTranslator->translateFilterName('nonexistingFilterName');
    }
}
