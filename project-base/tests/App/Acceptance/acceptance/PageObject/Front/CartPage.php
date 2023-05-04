<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;

class CartPage extends AbstractPage
{
    /**
     * @param string $productName
     * @param int $quantity
     */
    public function assertProductQuantity($productName, $quantity)
    {
        $quantityField = $this->getQuantityFieldByProductName($productName);
        $this->tester->seeInFieldByElement((string)$quantity, $quantityField);
    }

    /**
     * @param string $productName
     * @param string $price
     */
    public function assertProductPriceRoundedByCurrency($productName, $price)
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
    public function assertTotalPriceWithVatRoundedByCurrency($price)
    {
        $formattedPriceWithCurrency = $this->tester->getFormattedPriceWithCurrencySymbolRoundedByCurrencyOnFrontend(
            Money::create($price)
        );
        $orderPriceCell = $this->getTotalProductsPriceCell();
        $message = t('Total price including VAT', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->tester->getFrontendLocale());
        $this->tester->seeInElement($message . ': ' . $formattedPriceWithCurrency, $orderPriceCell);
    }

    /**
     * @param string $productName
     * @param int $quantity
     */
    public function changeProductQuantity($productName, $quantity)
    {
        $quantityField = $this->getQuantityFieldByProductName($productName);
        $this->tester->fillFieldByElement($quantityField, (string)$quantity);
        $this->tester->pressKeysByElement($quantityField, WebDriverKeys::ENTER);
        $this->tester->waitForAjax();
    }

    /**
     * @param string $productName
     */
    public function removeProductFromCart($productName)
    {
        $row = $this->findProductRowInCartByName($productName);
        $removingButton = $row->findElement(WebDriverBy::cssSelector('.test-cart-item-remove-button'));
        $this->tester->clickByElement($removingButton);
    }

    /**
     * @param string $productName
     */
    public function assertProductIsInCartByName($productName)
    {
        $translatedProductName = t($productName, [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->tester->getFrontendLocale());
        $this->tester->see($translatedProductName, WebDriverBy::cssSelector('.test-cart-item-name'));
    }

    /**
     * @param string $productName
     */
    public function assertProductIsNotInCartByName($productName)
    {
        $translatedProductName = t($productName, [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->tester->getFrontendLocale());
        $this->tester->dontSee($translatedProductName, WebDriverBy::cssSelector('.test-cart-item-name'));
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
        $translatedProductName = t($productName, [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->tester->getFrontendLocale());
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
    private function getProductTotalPriceCellByName($productName)
    {
        $row = $this->findProductRowInCartByName($productName);

        return $row->findElement(WebDriverBy::cssSelector('.test-cart-item-total-price'));
    }

    /**
     * @param string $productName
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function getProductPriceCellByName($productName)
    {
        $row = $this->findProductRowInCartByName($productName);

        return $row->findElement(WebDriverBy::cssSelector('.test-cart-item-price'));
    }

    /**
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function getTotalProductsPriceCell()
    {
        return $this->webDriver->findElement(WebDriverBy::cssSelector('.test-cart-total-price'));
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
        $removePromoCodeButton = $this->webDriver->findElement(
            WebDriverBy::cssSelector('#js-promo-code-remove-button')
        );
        $this->tester->clickByElement($removePromoCodeButton);
        $this->tester->waitForAjax();
    }

    public function canSeePromoCodeSubmitButtonElement()
    {
        $this->tester->seeElement(WebDriverBy::cssSelector('#js-promo-code-submit-button'));
    }

    public function canSeePromoCodeRemoveButtonElement()
    {
        $this->tester->canSeeElement(WebDriverBy::cssSelector('#js-promo-code-remove-button'));
    }

    /**
     * @param array $products
     * @param int $discount
     */
    public function assertTotalPriceWithVatByProducts(array $products, int $discount = 0)
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
        $productName = t($productName, [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->tester->getFrontendLocale());
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
            Translator::DEFAULT_TRANSLATION_DOMAIN,
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
        $productName = t($productName, [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->tester->getFrontendLocale());
        $this->tester->seeTranslationFrontend(
            'Product <strong>{{ name }}</strong> ({{ quantity|formatNumber }} {{ unitName }}) added to the cart',
            Translator::DEFAULT_TRANSLATION_DOMAIN,
            [
                '{{ name }}' => $productName,
                '{{ quantity|formatNumber }}' => $quantity,
                '{{ unitName }}' => $this->tester->getDefaultUnitName(),
            ]
        );
    }
}
