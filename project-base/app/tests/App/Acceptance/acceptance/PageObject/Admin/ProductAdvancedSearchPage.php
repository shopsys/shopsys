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

    public function search(string $searchSubject, string $value): void
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
            WebDriverBy::cssSelector('#js-advanced-search-rules-box'),
        );
    }

    public function assertFoundProductByName(string $productName): void
    {
        $translatedProductName = t($productName, [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->tester->getAdminLocale());
        $this->tester->seeTranslationAdminInCss($translatedProductName, '.test-grid-column-name');
    }

    public function assertFoundProductCount(int $expectedCount): void
    {
        $foundProductCount = $this->tester->countVisibleByCss('tbody .test-grid-row');

        $message = 'Product advanced search expected to found ' . $expectedCount . ' products but found ' . $foundProductCount . '.';
        Assert::assertSame($expectedCount, $foundProductCount, $message);
    }
}
