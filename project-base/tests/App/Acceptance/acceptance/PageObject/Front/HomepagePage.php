<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;
use Tests\App\Test\Codeception\AcceptanceTester;
use Tests\App\Test\Codeception\Module\StrictWebDriver;

class HomepagePage extends AbstractPage
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
    public function addTopProductToCartByName(string $productName, int $quantity = 1): void
    {
        $topProductsContext = $this->getTopProductsContext();

        $this->productListComponent->addProductToCartByName($productName, $quantity, $topProductsContext);
    }

    /**
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function getTopProductsContext(): WebDriverElement
    {
        return $this->webDriver->findElement(WebDriverBy::cssSelector('#top-products'));
    }
}
