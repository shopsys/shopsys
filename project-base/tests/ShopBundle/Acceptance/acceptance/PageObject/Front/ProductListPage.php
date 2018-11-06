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

    /**
     * @param \Tests\ShopBundle\Test\Codeception\Module\StrictWebDriver $strictWebDriver
     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $tester
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\ProductListComponent $productListComponent
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
    public function addProductToCartByName($productName, $quantity = 1)
    {
        $context = $this->getProductListCompomentContext();

        $this->productListComponent->addProductToCartByName($productName, $quantity, $context);
    }

    /**
     * @param int $expectedCount
     */
    public function assertProductsTotalCount($expectedCount)
    {
        $totalCountElement = $this->getProductListCompomentContext()
            ->findElement(WebDriverBy::cssSelector('.js-paging-total-count'));
        $actualCount = (int)trim($totalCountElement->getText());

        $message = 'Product list expects ' . $expectedCount . ' products but contains ' . $actualCount . '.';
        Assert::assertSame($expectedCount, $actualCount, $message);
    }

    /**
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function getProductListCompomentContext()
    {
        return $this->webDriver->findElement(WebDriverBy::cssSelector('.web__main__content'));
    }
}
