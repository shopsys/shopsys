<?php

namespace Tests\ShopBundle\Acceptance\acceptance\PageObject\Front;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Tests\ShopBundle\Acceptance\acceptance\PageObject\AbstractPage;

class CartPage extends AbstractPage
{
    public function assertProductQuantity(string $productName, int $quantity): void
    {
        $quantityField = $this->getQuantityFieldByProductName($productName);
        $this->tester->seeInFieldByElement($quantity, $quantityField);
    }
    
    public function assertProductPrice(string $productName, string $formattedPriceWithCurrency): void
    {
        $productPriceCell = $this->getProductPriceCellByName($productName);
        $this->tester->seeInElement($formattedPriceWithCurrency, $productPriceCell);
    }
    
    public function assertTotalPriceWithVat(string $formattedPriceWithCurrency): void
    {
        $orderPriceCell = $this->getTotalProductsPriceCell();
        $this->tester->seeInElement('Total price including VAT: ' . $formattedPriceWithCurrency, $orderPriceCell);
    }
    
    public function changeProductQuantity(string $productName, int $quantity): void
    {
        $quantityField = $this->getQuantityFieldByProductName($productName);
        $this->tester->fillFieldByElement($quantityField, $quantity);
        $this->tester->pressKeysByElement($quantityField, WebDriverKeys::ENTER);
        $this->tester->waitForAjax();
    }
    
    public function removeProductFromCart(string $productName): void
    {
        $row = $this->findProductRowInCartByName($productName);
        $removingButton = $row->findElement(WebDriverBy::cssSelector('.js-cart-item-remove-button'));
        $this->tester->clickByElement($removingButton);
    }
    
    public function assertProductIsInCartByName(string $productName): void
    {
        $this->tester->see($productName, WebDriverBy::cssSelector('.js-cart-item-name'));
    }
    
    public function assertProductIsNotInCartByName(string $productName): void
    {
        $this->tester->dontSee($productName, WebDriverBy::cssSelector('.js-cart-item-name'));
    }
    
    private function getQuantityFieldByProductName(string $productName): \Facebook\WebDriver\WebDriverElement
    {
        $row = $this->findProductRowInCartByName($productName);

        return $row->findElement(WebDriverBy::cssSelector('input[name^="cart_form[quantities]"]'));
    }
    
    private function findProductRowInCartByName(string $productName): \Facebook\WebDriver\WebDriverElement
    {
        $rows = $this->webDriver->findElements(WebDriverBy::cssSelector('.js-cart-item'));

        foreach ($rows as $row) {
            try {
                $nameCell = $row->findElement(WebDriverBy::cssSelector('.js-cart-item-name'));

                if ($nameCell->getText() === $productName) {
                    return $row;
                }
            } catch (\Facebook\WebDriver\Exception\NoSuchElementException $ex) {
                continue;
            }
        }

        $message = 'Unable to find row containing product "' . $productName . '" in cart.';
        throw new \Facebook\WebDriver\Exception\NoSuchElementException($message);
    }
    
    private function getProductPriceCellByName(string $productName): \Facebook\WebDriver\WebDriverElement
    {
        $row = $this->findProductRowInCartByName($productName);

        return $row->findElement(WebDriverBy::cssSelector('.js-cart-item-total-price'));
    }

    private function getTotalProductsPriceCell(): \Facebook\WebDriver\WebDriverElement
    {
        return $this->webDriver->findElement(WebDriverBy::cssSelector('.js-cart-total-price'));
    }
}
