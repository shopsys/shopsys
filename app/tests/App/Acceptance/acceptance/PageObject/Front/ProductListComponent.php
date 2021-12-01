<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;
use Tests\App\Test\Codeception\AcceptanceTester;
use Tests\App\Test\Codeception\Module\StrictWebDriver;

class ProductListComponent extends AbstractPage
{
    /**
     * @var \Tests\App\Acceptance\acceptance\PageObject\Front\ProductDetailPage
     */
    private ProductDetailPage $productDetailPage;

    /**
     * @param \Tests\App\Test\Codeception\Module\StrictWebDriver $strictWebDriver
     * @param \Tests\App\Test\Codeception\AcceptanceTester $tester
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductDetailPage $productDetailPage
     */
    public function __construct(
        StrictWebDriver $strictWebDriver,
        AcceptanceTester $tester,
        ProductDetailPage $productDetailPage
    ) {
        parent::__construct($strictWebDriver, $tester);

        $this->productDetailPage = $productDetailPage;
    }

    /**
     * @param string $productName
     * @param int $quantity
     * @param \Facebook\WebDriver\WebDriverElement $context
     */
    public function addProductToCartByName($productName, $quantity, WebDriverElement $context)
    {
        $productItemElement = $this->findProductListItemByName($productName, $context);
        $this->tester->clickByElement($productItemElement);

        $this->productDetailPage->addProductIntoCart($quantity);
    }

    /**
     * @param string $productName
     * @param \Facebook\WebDriver\WebDriverElement $context
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function findProductListItemByName($productName, WebDriverElement $context)
    {
        $translatedProductName = t($productName, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $productItems = $context->findElements(WebDriverBy::cssSelector('.js-list-products-item'));

        foreach ($productItems as $item) {
            try {
                $titleElement = $item->findElement(WebDriverBy::cssSelector('.js-list-products-item-title'));
                $nameElement = $titleElement->findElement(WebDriverBy::cssSelector('.js-list-products-item-name'));

                if (rtrim($nameElement->getText(), ',') === $translatedProductName) {
                    return $item;
                }
            } catch (NoSuchElementException $ex) {
                continue;
            }
        }

        $message = sprintf(
            'Unable to find product "%s" (translated to "%s") in product list component.',
            $productName,
            $translatedProductName
        );

        throw new NoSuchElementException($message);
    }
}
