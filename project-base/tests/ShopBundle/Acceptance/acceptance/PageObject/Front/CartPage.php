<?php

namespace Tests\ShopBundle\Acceptance\acceptance\PageObject\Front;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Tests\ShopBundle\Acceptance\acceptance\PageObject\AbstractPage;

class CartPage extends AbstractPage
{
    /**
     * @param string $productName
     * @param int $quantity
     */
    public function assertProductQuantity($productName, $quantity)
    {
        $quantityField = $this->getQuantityFieldByProductName($productName);
        $this->tester->seeInFieldByElement($quantity, $quantityField);
    }

    /**
     * @param string $productName
     * @param string $formattedPriceWithCurrency
     */
    public function assertProductPrice($productName, $formattedPriceWithCurrency)
    {
        $productPriceCell = $this->getProductPriceCellByName($productName);
        $this->tester->seeInElement($formattedPriceWithCurrency, $productPriceCell);
    }

    /**
     * @param string $formattedPriceWithCurrency
     */
    public function assertTotalPriceWithVat($formattedPriceWithCurrency)
    {
        $orderPriceCell = $this->getTotalProductsPriceCell();
        $this->tester->seeInElement('Total price including VAT: ' . $formattedPriceWithCurrency, $orderPriceCell);
    }

    /**
     * @param string $productName
     * @param int $quantity
     */
    public function changeProductQuantity($productName, $quantity)
    {
        $quantityField = $this->getQuantityFieldByProductName($productName);
        $this->tester->fillFieldByElement($quantityField, $quantity);
        $this->tester->pressKeysByElement($quantityField, WebDriverKeys::ENTER);
        $this->tester->waitForAjax();
    }

    /**
     * @param string $productName
     */
    public function removeProductFromCart($productName)
    {
        $row = $this->findProductRowInCartByName($productName);
        $removingButton = $row->findElement(WebDriverBy::cssSelector('.js-cart-item-remove-button'));
        $this->tester->clickByElement($removingButton);
    }

    /**
     * @param string $productName
     */
    public function assertProductIsInCartByName($productName)
    {
        $this->tester->see($productName, WebDriverBy::cssSelector('.js-cart-item-name'));
    }

    /**
     * @param string $productName
     */
    public function assertProductIsNotInCartByName($productName)
    {
        $this->tester->dontSee($productName, WebDriverBy::cssSelector('.js-cart-item-name'));
    }

    /**
     * @param string $productName
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function getQuantityFieldByProductName($productName)
    {
        $row = $this->findProductRowInCartByName($productName);

        return $row->findElement(WebDriverBy::cssSelector('input[name^="cart_form[quantities]"]'));
    }

    /**
     * @param string $productName
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function findProductRowInCartByName($productName)
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

    /**
     * @param string $productName
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function getProductPriceCellByName($productName)
    {
        $row = $this->findProductRowInCartByName($productName);

        return $row->findElement(WebDriverBy::cssSelector('.js-cart-item-total-price'));
    }

    /**
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function getTotalProductsPriceCell()
    {
        return $this->webDriver->findElement(WebDriverBy::cssSelector('.js-cart-total-price'));
    }

    /**
     * @param string $promoCodeName
     */
    public function applyPromoCode($promoCodeName)
    {
        $promoCodeField = $this->webDriver->findElement(WebDriverBy::cssSelector('#js-promo-code-input'));
        $this->tester->fillFieldByElement($promoCodeField, $promoCodeName);
        $this->tester->pressKeysByElement($promoCodeField, WebDriverKeys::ENTER);
        $this->tester->waitForAjax();
    }

    public function removePromoCode()
    {
        $removePromoCodeButton = $this->webDriver->findElement(WebDriverBy::cssSelector('#js-promo-code-remove-button'));
        $this->tester->clickByElement($removePromoCodeButton);
        $this->tester->waitForAjax();
    }

    /**
     * @return \Facebook\WebDriver\WebDriverElement
     */
    public function canSeePromoCodeSubmitButtonElement()
    {
        return $this->tester->seeElement(WebDriverBy::cssSelector('#js-promo-code-submit-button'));
    }

    /**
     * @return \Facebook\WebDriver\WebDriverElement
     */
    public function canSeePromoCodeRemoveButtonElement()
    {
        return $this->tester->canSeeElement(WebDriverBy::cssSelector('#js-promo-code-remove-button'));
    }
}
