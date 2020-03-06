<?php

declare(strict_types=1);

namespace Tests\App\Functional\Form\Admin\AdvancedSearch;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOrderFilterTranslation;
use Tests\App\Test\FunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class AdvancedSearchOrderFilterTranslationTest extends FunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\AdvancedSearch\OrderAdvancedSearchConfig
     * @inject
     */
    private $advancedSearchConfig;

    /**
     * @var \Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOrderFilterTranslation
     * @inject
     */
    private $advancedSearchOrderFilterTranslation;

    public function testTranslateFilterName()
    {
        foreach ($this->advancedSearchConfig->getAllFilters() as $filter) {
            $this->assertNotEmpty($this->advancedSearchOrderFilterTranslation->translateFilterName($filter->getName()));
        }
    }

    public function testTranslateFilterNameNotFoundException()
    {
        $advancedSearchTranslator = new AdvancedSearchOrderFilterTranslation();

        $this->expectException(\Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchTranslationNotFoundException::class);
        $advancedSearchTranslator->translateFilterName('nonexistingFilterName');
    }
}
