<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Admin;

use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\Assert;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;

class ProductAdvancedSearchPage extends AbstractPage
{
    public const SEARCH_SUBJECT_CATNUM = 'productCatnum';

    /**
     * @param string $searchSubject
     * @param string $value
     */
    public function search($searchSubject, $value)
    {
        $this->tester->amOnPage('/admin/product/list/');

        $this->tester->clickByTranslationAdmin('Advanced search');
        $this->tester->selectOptionInSelect2ByCssAndValue('.test-advanced-search-rule-subject', $searchSubject);
        $this->tester->waitForAjax();
        $this->tester->fillFieldByCss('.test-advanced-search-rule-value input', $value);

        $this->tester->clickByTranslationAdmin(
            'Search [verb]',
            Translator::DEFAULT_TRANSLATION_DOMAIN,
            [],
            WebDriverBy::cssSelector('#js-advanced-search-rules-box')
        );
    }

    /**
     * @param string $productName
     */
    public function assertFoundProductByName($productName)
    {
        $translatedProductName = t($productName, [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->tester->getAdminLocale());
        $this->tester->seeTranslationAdminInCss($translatedProductName, '.test-grid-column-name');
    }

    /**
     * @param int $expectedCount
     */
    public function assertFoundProductCount($expectedCount)
    {
        $foundProductCount = $this->tester->countVisibleByCss('tbody .table-grid__row');

        $message = 'Product advanced search expected to found ' . $expectedCount . ' products but found ' . $foundProductCount . '.';
        Assert::assertSame($expectedCount, $foundProductCount, $message);
    }
}
