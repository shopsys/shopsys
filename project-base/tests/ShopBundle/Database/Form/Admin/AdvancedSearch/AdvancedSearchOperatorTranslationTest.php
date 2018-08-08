<?php

namespace Tests\ShopBundle\Database\Form\Admin\AdvancedSearch;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\ProductAdvancedSearchConfig;
use Tests\ShopBundle\Test\FunctionalTestCase;

class AdvancedSearchOperatorTranslationTest extends FunctionalTestCase
{
    public function testTranslateOperator(): void
    {
        $productAdvancedSearchConfig = $this->getContainer()->get(ProductAdvancedSearchConfig::class);
        /* @var $productAdvancedSearchConfig \Shopsys\FrameworkBundle\Model\AdvancedSearch\ProductAdvancedSearchConfig */
        $orderAdvancedSearchConfig = $this->getContainer()->get(ProductAdvancedSearchConfig::class);
        /* @var $orderAdvancedSearchConfig \Shopsys\FrameworkBundle\Model\AdvancedSearch\OrderAdvancedSearchConfig */

        $advancedSearchOperatorTranslation = $this->getContainer()->get(AdvancedSearchOperatorTranslation::class);
        /* @var $advancedSearchOperatorTranslation \Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation */

        $operators = [];
        foreach ($productAdvancedSearchConfig->getAllFilters() as $filter) {
            $operators = array_merge($operators, $filter->getAllowedOperators());
        }
        foreach ($orderAdvancedSearchConfig->getAllFilters() as $filter) {
            $operators = array_merge($operators, $filter->getAllowedOperators());
        }

        foreach ($operators as $operator) {
            $this->assertNotEmpty($advancedSearchOperatorTranslation->translateOperator($operator));
        }
    }

    public function testTranslateOperatorNotFoundException(): void
    {
        $advancedSearchTranslator = new AdvancedSearchOperatorTranslation();

        $this->expectException(\Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchTranslationNotFoundException::class);
        $advancedSearchTranslator->translateOperator('nonexistingOperator');
    }
}
