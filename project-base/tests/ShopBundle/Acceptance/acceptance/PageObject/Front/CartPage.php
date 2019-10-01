<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Acceptance\acceptance\PageObject\Front;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Shopsys\FrameworkBundle\Component\Money\Money;
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
     * @param string $price
     */
    public function assertProductPrice($productName, $price)
    {
        $formattedPriceWithCurrency = $this->tester->getFormattedPriceWithCurrencySymbolOnFrontend(Money::create($price));
        $productPriceCell = $this->getProductPriceCellByName($productName);
        $this->tester->seeInElement($formattedPriceWithCurrency, $productPriceCell);
    }

    /**
     * @param string $price
     */
    public function assertTotalPriceWithVat($price)
    {
        $formattedPriceWithCurrency = $this->tester->getFormattedPriceWithCurrencySymbolOnFrontend(Money::create($price));
        $orderPriceCell = $this->getTotalProductsPriceCell();
        $message = t('Total price including VAT', [], 'messages', $this->tester->getFrontendLocale());
        $this->tester->seeInElement($message . ': ' . $formattedPriceWithCurrency, $orderPriceCell);
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
        $translatedProductName = t($productName, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $this->tester->see($translatedProductName, WebDriverBy::cssSelector('.js-cart-item-name'));
    }

    /**
     * @param string $productName
     */
    public function assertProductIsNotInCartByName($productName)
    {
        $translatedProductName = t($productName, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $this->tester->dontSee($translatedProductName, WebDriverBy::cssSelector('.js-cart-item-name'));
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
        $translatedProductName = t($productName, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $rows = $this->webDriver->findElements(WebDriverBy::cssSelector('.js-cart-item'));

        foreach ($rows as $row) {
            try {
                $nameCell = $row->findElement(WebDriverBy::cssSelector('.js-cart-item-name'));

                if ($nameCell->getText() === $translatedProductName) {
                    return $row;
                }
            } catch (\Facebook\WebDriver\Exception\NoSuchElementException $ex) {
                continue;
            }
        }

        $message = sprintf('Unable to find row containing product "%s" (translated to "%s") in cart.', $productName, $translatedProductName);
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
