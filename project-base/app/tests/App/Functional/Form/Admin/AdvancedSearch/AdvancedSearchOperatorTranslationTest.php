<?php

declare(strict_types=1);

namespace Tests\App\Functional\Form\Admin\AdvancedSearch;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchTranslationNotFoundException;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AdvancedSearchFilterRegistry;
use Tests\App\Test\FunctionalTestCase;

class AdvancedSearchOperatorTranslationTest extends FunctionalTestCase
{
    /**
     * @inject
     */
    private AdvancedSearchFilterRegistry $filterRegistry;

    /**
     * @inject
     */
    private AdvancedSearchOperatorTranslation $advancedSearchOperatorTranslation;

    public function testAllUsedOperatorsHaveTranslations(): void
    {
        $operators = [];

        foreach ($this->filterRegistry->getAllFilters() as $filter) {
            foreach ($filter->getAllowedOperators() as $operator) {
                $operators[$operator] = $operator;
            }
        }

        foreach ($operators as $operator) {
            $this->assertNotEmpty($this->advancedSearchOperatorTranslation->translateOperator($operator));
        }
    }

    public function testTranslateOperatorNotFoundException(): void
    {
        $this->expectException(AdvancedSearchTranslationNotFoundException::class);

        // Create a mock enum-like value that doesn't exist
        $this->advancedSearchOperatorTranslation->translateOperator('nonexistingOperator');
    }
}
