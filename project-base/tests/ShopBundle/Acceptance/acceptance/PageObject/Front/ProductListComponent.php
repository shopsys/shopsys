<?php

namespace Tests\ShopBundle\Acceptance\acceptance\PageObject\Front;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Tests\ShopBundle\Acceptance\acceptance\PageObject\AbstractPage;

class ProductListComponent extends AbstractPage
{
    public function addProductToCartByName(string $productName, int $quantity, WebDriverElement $context): void
    {
        $productItemElement = $this->findProductListItemByName($productName, $context);

        $quantityElement = $productItemElement->findElement(WebDriverBy::name('add_product_form[quantity]'));
        $addButtonElement = $productItemElement->findElement(WebDriverBy::name('add_product_form[add]'));

        $this->tester->fillFieldByElement($quantityElement, $quantity);
        $this->tester->clickByElement($addButtonElement);
        $this->tester->waitForAjax();
        $this->tester->wait(1); // animation of popup window
    }

    private function findProductListItemByName(string $productName, WebDriverElement $context): \Facebook\WebDriver\WebDriverElement
    {
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

        $message = 'Unable to find product "' . $productName . '" in product list component.';
        throw new \Facebook\WebDriver\Exception\NoSuchElementException($message);
    }
}
