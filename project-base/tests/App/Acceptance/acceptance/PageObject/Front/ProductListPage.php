<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\Assert;
use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;
use Tests\App\Test\Codeception\AcceptanceTester;
use Tests\App\Test\Codeception\Module\StrictWebDriver;

class ProductListPage extends AbstractPage
{
    /**
     * @var \Tests\App\Acceptance\acceptance\PageObject\Front\ProductListComponent
     */
    private $productListComponent;

    /**
     * @param \Tests\App\Test\Codeception\Module\StrictWebDriver $strictWebDriver
     * @param \Tests\App\Test\Codeception\AcceptanceTester $tester
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductListComponent $productListComponent
     */
    public function __construct(
        StrictWebDriver $strictWebDriver,
        AcceptanceTester $tester,
        ProductListComponent $productListComponent
    ) {
        $this->productListComponent = $productListComponent;

        parent::__construct($strictWebDriver, $tester);
    }

    /**
     * @param string $productName
     * @param int $quantity
     */
    public function addProductToCartByName(string $productName, int $quantity = 1): void
    {
        $context = $this->getProductListCompomentContext();

        $this->productListComponent->addProductToCartByName($productName, $quantity, $context);

        $this->tester->waitForAjax();
        $this->tester->wait(1);
    }

    /**
     * @param int $expectedCount
     */
    public function assertProductsTotalCount(int $expectedCount): void
    {
        $totalCountElement = $this->getProductListCompomentContext()
            ->findElement(WebDriverBy::cssSelector('.test-paging-total-count'));
        $actualCount = (int)trim($totalCountElement->getText());

        $message = 'Product list expects ' . $expectedCount . ' products but contains ' . $actualCount . '.';
        Assert::assertSame($expectedCount, $actualCount, $message);
    }

    /**
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function getProductListCompomentContext(): \Facebook\WebDriver\WebDriverElement
    {
        return $this->webDriver->findElement(WebDriverBy::cssSelector('.web__main__content'));
    }
}
