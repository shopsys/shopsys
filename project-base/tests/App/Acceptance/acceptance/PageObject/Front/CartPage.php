<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;

class CartPage extends AbstractPage
{
    /**
     * @param string $productName
     * @param int $quantity
     */
    public function assertProductQuantity(string $productName, int $quantity): void
    {
        $quantityField = $this->getQuantityFieldByProductName($productName);
        $this->tester->seeInFieldByElement((string)$quantity, $quantityField);
    }

    /**
     * @param string $productName
     * @param string $price
     */
    public function assertProductPriceRoundedByCurrency(string $productName, string $price): void
    {
        $convertedPrice = $this->tester->getPriceWithVatConvertedToDomainDefaultCurrency($price);
        $formattedPriceWithCurrency = $this->tester->getFormattedPriceWithCurrencySymbolRoundedByCurrencyOnFrontend(
            Money::create($convertedPrice)
        );
        $productPriceCell = $this->getProductTotalPriceCellByName($productName);
        $this->tester->seeInElement($formattedPriceWithCurrency, $productPriceCell);
    }

    /**
     * @param string $price
     */
    public function assertTotalPriceWithVatRoundedByCurrency(string $price): void
    {
        $formattedPriceWithCurrency = $this->tester->getFormattedPriceWithCurrencySymbolRoundedByCurrencyOnFrontend(
            Money::create($price)
        );
        $orderPriceCell = $this->getTotalProductsPriceCell();
        $message = t('Total price including VAT', [], 'messages', $this->tester->getFrontendLocale());
        $this->tester->seeInElement($message . ': ' . $formattedPriceWithCurrency, $orderPriceCell);
    }

    /**
     * @param string $productName
     * @param int $quantity
     */
    public function changeProductQuantity(string $productName, int $quantity): void
    {
        $quantityField = $this->getQuantityFieldByProductName($productName);
        $this->tester->fillFieldByElement($quantityField, (string)$quantity);
        $this->tester->pressKeysByElement($quantityField, WebDriverKeys::ENTER);
        $this->tester->waitForAjax();
    }

    /**
     * @param string $productName
     */
    public function removeProductFromCart(string $productName): void
    {
        $row = $this->findProductRowInCartByName($productName);
        $removingButton = $row->findElement(WebDriverBy::cssSelector('.test-cart-item-remove-button'));
        $this->tester->clickByElement($removingButton);
    }

    /**
     * @param string $productName
     */
    public function assertProductIsInCartByName(string $productName): void
    {
        $translatedProductName = t($productName, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $this->tester->see($translatedProductName, WebDriverBy::cssSelector('.test-cart-item-name'));
    }

    /**
     * @param string $productName
     */
    public function assertProductIsNotInCartByName(string $productName): void
    {
        $translatedProductName = t($productName, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $this->tester->dontSee($translatedProductName, WebDriverBy::cssSelector('.test-cart-item-name'));
    }

    /**
     * @param string $productName
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function getQuantityFieldByProductName(\string $productName): \Facebook\WebDriver\WebDriverElement
    {
        $row = $this->findProductRowInCartByName($productName);

        return $row->findElement(WebDriverBy::cssSelector('input[name^="cart_form[quantities]"]'));
    }

    /**
     * @param string $productName
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function findProductRowInCartByName(\string $productName): \Facebook\WebDriver\WebDriverElement
    {
        $translatedProductName = t($productName, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $rows = $this->webDriver->findElements(WebDriverBy::cssSelector('.test-cart-item'));

        foreach ($rows as $row) {
            try {
                $nameCell = $row->findElement(WebDriverBy::cssSelector('.test-cart-item-name'));

                if ($nameCell->getText() === $translatedProductName) {
                    return $row;
                }
            } catch (NoSuchElementException $ex) {
                continue;
            }
        }

        $message = sprintf(
            'Unable to find row containing product "%s" (translated to "%s") in cart.',
            $productName,
            $translatedProductName
        );
        throw new NoSuchElementException($message);
    }

    /**
     * @param string $productName
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function getProductTotalPriceCellByName(\string $productName): \Facebook\WebDriver\WebDriverElement
    {
        $row = $this->findProductRowInCartByName($productName);

        return $row->findElement(WebDriverBy::cssSelector('.test-cart-item-total-price'));
    }

    /**
     * @param string $productName
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function getProductPriceCellByName(string $productName): \Facebook\WebDriver\WebDriverElement
    {
        $row = $this->findProductRowInCartByName($productName);

        return $row->findElement(WebDriverBy::cssSelector('.test-cart-item-price'));
    }

    /**
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function getTotalProductsPriceCell(): \Facebook\WebDriver\WebDriverElement
    {
        return $this->webDriver->findElement(WebDriverBy::cssSelector('.test-cart-total-price'));
    }

    /**
     * @param string $promoCodeName
     */
    public function applyPromoCode(string $promoCodeName): void
    {
        $promoCodeField = $this->webDriver->findElement(WebDriverBy::cssSelector('#js-promo-code-input'));
        $this->tester->fillFieldByElement($promoCodeField, $promoCodeName);
        $this->tester->pressKeysByElement($promoCodeField, WebDriverKeys::ENTER);
        $this->tester->waitForAjax();
    }

    public function removePromoCode(): void
    {
        $removePromoCodeButton = $this->webDriver->findElement(
            WebDriverBy::cssSelector('#js-promo-code-remove-button')
        );
        $this->tester->clickByElement($removePromoCodeButton);
        $this->tester->waitForAjax();
    }

    /**
     * @return \Facebook\WebDriver\WebDriverElement
     */
    public function canSeePromoCodeSubmitButtonElement(): \Facebook\WebDriver\WebDriverElement
    {
        return $this->tester->seeElement(WebDriverBy::cssSelector('#js-promo-code-submit-button'));
    }

    /**
     * @return \Facebook\WebDriver\WebDriverElement
     */
    public function canSeePromoCodeRemoveButtonElement(): \Facebook\WebDriver\WebDriverElement
    {
        return $this->tester->canSeeElement(WebDriverBy::cssSelector('#js-promo-code-remove-button'));
    }

    /**
     * @param array $products
     * @param int $discount
     */
    public function assertTotalPriceWithVatByProducts(array $products, int $discount = 0): void
    {
        $totalPrice = Money::zero();

        foreach ($products as $productName => $count) {
            $totalPrice = $totalPrice->add(
                Money::create($this->getProductTotalPriceByName($productName))
                    ->divide(100, 6)
                    ->multiply(100 - $discount)
                    ->multiply($count)
            );
        }

        $this->assertTotalPriceWithVatRoundedByCurrency($totalPrice->getAmount());
    }

    /**
     * @param string $productName
     * @return string
     */
    private function getProductTotalPriceByName(string $productName): string
    {
        $productName = t($productName, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $productPriceCell = $this->getProductPriceCellByName($productName);

        $productPriceWithoutCurrencySymbol = preg_replace('/[^0-9.,]/', '', $productPriceCell->getText());

        return $this->tester->getNumberFromLocalizedFormat(
            $productPriceWithoutCurrencySymbol,
            $this->tester->getFrontendLocale()
        );
    }

    public function clickGoToCartInPopUpWindow(): void
    {
        $this->tester->clickByTranslationFrontend(
            'Go to cart',
            'messages',
            [],
            WebDriverBy::cssSelector('#window-main-container')
        );
    }

    /**
     * @param string $productName
     * @param int $quantity
     */
    public function seeSuccessMessageForAddedProducts(string $productName, int $quantity): void
    {
        $productName = t($productName, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $this->tester->seeTranslationFrontend(
            'Product <strong>{{ name }}</strong> ({{ quantity|formatNumber }} {{ unitName }}) added to the cart',
            'messages',
            [
                '{{ name }}' => $productName,
                '{{ quantity|formatNumber }}' => $quantity,
                '{{ unitName }}' => $this->tester->getDefaultUnitName(),
            ]
        );
    }
}
