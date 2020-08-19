<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;

class ProductListComponent extends AbstractPage
{
    /**
     * @param string $productName
     * @param int $quantity
     * @param \Facebook\WebDriver\WebDriverElement $context
     */
    public function addProductToCartByName($productName, $quantity, WebDriverElement $context)
    {
        $productItemElement = $this->findProductListItemByName($productName, $context);

        $quantityElement = $productItemElement->findElement(WebDriverBy::name('add_product_form[quantity]'));
        $addButtonElement = $productItemElement->findElement(WebDriverBy::name('add_product_form[add]'));

        $this->tester->fillFieldByElement($quantityElement, (string)$quantity);
        $this->tester->clickByElement($addButtonElement);
        $this->tester->waitForAjax();
        $this->tester->wait(1); // animation of popup window
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
                $nameElement = $item->findElement(WebDriverBy::cssSelector('.js-list-products-item-title'));

                if ($nameElement->getText() === $productName) {
                    return $item;
                }
            } catch (\Facebook\WebDriver\Exception\NoSuchElementException $ex) {
                continue;
            }
        }

        $message = sprintf('Unable to find product "%s" (translated to "%s") in product list component.', $productName, $translatedProductName);
        throw new \Facebook\WebDriver\Exception\NoSuchElementException($message);
    }
}
