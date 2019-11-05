<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Facebook\WebDriver\WebDriverBy;
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
    public function addTopProductToCartByName($productName, $quantity = 1)
    {
        $topProductsContext = $this->getTopProductsContext();

        $this->productListComponent->addProductToCartByName($productName, $quantity, $topProductsContext);
    }

    /**
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function getTopProductsContext()
    {
        return $this->webDriver->findElement(WebDriverBy::cssSelector('#top-products'));
    }
}
