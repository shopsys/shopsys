<?php

declare(strict_types=1);

namespace Tests\App\Functional\Form\Admin\AdvancedSearch;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchTranslationNotFoundException;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\OrderAdvancedSearchConfig;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\ProductAdvancedSearchConfig;
use Tests\App\Test\FunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class AdvancedSearchOperatorTranslationTest extends FunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @inject
     */
    private ProductAdvancedSearchConfig $productAdvancedSearchConfig;

    /**
     * @inject
     */
    private OrderAdvancedSearchConfig $orderAdvancedSearchConfig;

    /**
     * @inject
     */
    private AdvancedSearchOperatorTranslation $advancedSearchOperatorTranslation;

    public function testTranslateOperator()
    {
        $operators = [];
        foreach ($this->productAdvancedSearchConfig->getAllFilters() as $filter) {
            $operators = array_merge($operators, $filter->getAllowedOperators());
        }
        foreach ($this->orderAdvancedSearchConfig->getAllFilters() as $filter) {
            $operators = array_merge($operators, $filter->getAllowedOperators());
        }

        foreach ($operators as $operator) {
            $this->assertNotEmpty($this->advancedSearchOperatorTranslation->translateOperator($operator));
        }
    }

    public function testTranslateOperatorNotFoundException()
    {
        $advancedSearchTranslator = new AdvancedSearchOperatorTranslation();

        $this->expectException(AdvancedSearchTranslationNotFoundException::class);
        $advancedSearchTranslator->translateOperator('nonexistingOperator');
    }
}
