<?php

namespace Tests\ShopBundle\Acceptance\acceptance\PageObject\Admin;

use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\Assert;
use Tests\ShopBundle\Acceptance\acceptance\PageObject\AbstractPage;

class ProductAdvancedSearchPage extends AbstractPage
{
    const SEARCH_SUBJECT_CATNUM = 'productCatnum';
    
    public function search(string $searchSubject, string $value): void
    {
        $this->tester->amOnPage('/admin/product/list/');

        $this->tester->clickByText('Advanced search');
        $this->tester->selectOptionByCssAndValue('.js-advanced-search-rule-subject', $searchSubject);
        $this->tester->waitForAjax();
        $this->tester->fillFieldByCss('.js-advanced-search-rule-value input', $value);

        $this->tester->clickByText('Search', WebDriverBy::cssSelector('#js-advanced-search-rules-box'));
    }
    
    public function assertFoundProductByName(string $productName): void
    {
        $this->tester->seeInCss($productName, '.js-grid-column-name');
    }
    
    public function assertFoundProductCount(int $expectedCount): void
    {
        $foundProductCount = $this->tester->countVisibleByCss('tbody .table-grid__row');

        $message = 'Product advanced search expected to found ' . $expectedCount . ' products but found ' . $foundProductCount . '.';
        Assert::assertSame($expectedCount, $foundProductCount, $message);
    }
}
