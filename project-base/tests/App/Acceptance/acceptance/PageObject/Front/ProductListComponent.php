<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Facebook\WebDriver\Exception\NoSuchElementException;
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
        // animation of popup window
        $this->tester->wait(1);
    }

    /**
     * @param string $productName
     * @param \Facebook\WebDriver\WebDriverElement $context
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function findProductListItemByName($productName, WebDriverElement $context)
    {
        $translatedProductName = t($productName, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $productItems = $context->findElements(WebDriverBy::cssSelector('.test-list-products-item'));

        foreach ($productItems as $item) {
            try {
                $nameElement = $item->findElement(WebDriverBy::cssSelector('.test-list-products-item-title'));

                if ($nameElement->getText() === $productName) {
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
