<?php

namespace Tests\ShopBundle\Acceptance\acceptance\PageObject\Front;

use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\Assert;
use Tests\ShopBundle\Acceptance\acceptance\PageObject\AbstractPage;
use Tests\ShopBundle\Test\Codeception\AcceptanceTester;
use Tests\ShopBundle\Test\Codeception\Module\StrictWebDriver;

class ProductListPage extends AbstractPage
{
    /**
     * @var \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\ProductListComponent
     */
    private $productListComponent;

    public function __construct(
        StrictWebDriver $strictWebDriver,
        AcceptanceTester $tester,
        ProductListComponent $productListComponent
    ) {
        $this->productListComponent = $productListComponent;
        parent::__construct($strictWebDriver, $tester);
    }

    public function addProductToCartByName(string $productName, int $quantity = 1): void
    {
        $context = $this->getProductListCompomentContext();

        $this->productListComponent->addProductToCartByName($productName, $quantity, $context);
    }

    public function assertProductsTotalCount(int $expectedCount): void
    {
        $totalCountElement = $this->getProductListCompomentContext()
            ->findElement(WebDriverBy::cssSelector('.js-paging-total-count'));
        $actualCount = (int)trim($totalCountElement->getText());

        $message = 'Product list expects ' . $expectedCount . ' products but contains ' . $actualCount . '.';
        Assert::assertSame($expectedCount, $actualCount, $message);
    }

    private function getProductListCompomentContext(): \Facebook\WebDriver\WebDriverElement
    {
        return $this->webDriver->findElement(WebDriverBy::cssSelector('.web__main__content'));
    }
}
