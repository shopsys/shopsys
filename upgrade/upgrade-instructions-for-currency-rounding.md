# Upgrade Instructions for rounding by Currency

There is a new way of rounding prices. Previously global rounding setting was removed.
Rounding depends on currency settings, which can be managed by administrator. In addition, administrator 
can manage minimum fraction digits, which will be displayed on fronted, for every currency separately.

To avoid of [BC breaks](/docs/contributing/backward-compatibility-promise.md), new functions for rounding by currency have been implemented.
Because of new functions, new tests have been introduced.

### Data fixture
- set up default settings for demo currencies in `CurrencyDataFixture`
```diff
         /**
          * The "CZK" currency is created in database migration.
          * @see \Shopsys\FrameworkBundle\Migrations\Version20180603135342
          */
         $currencyCzk = $this->currencyFacade->getById(1);
+        $currencyData = $this->currencyDataFactory->createFromCurrency($currencyCzk);
+        $currencyData->minFractionDigits = Currency::DEFAULT_MIN_FRACTION_DIGITS;
+        $currencyData->roundingType = Currency::ROUNDING_TYPE_INTEGER;
+        $currencyCzk = $this->currencyFacade->edit($currencyCzk->getId(), $currencyData);
         $this->addReference(self::CURRENCY_CZK, $currencyCzk);
```
```diff
          $currencyData->code = Currency::CODE_EUR;
          $currencyData->exchangeRate = '25';
+         $currencyData->minFractionDigits = Currency::DEFAULT_MIN_FRACTION_DIGITS;
+         $currencyData->roundingType = Currency::ROUNDING_TYPE_HUNDREDTHS;
          $currencyEuro = $this->currencyFacade->create($currencyData);
```
- If you would like to use different settings in your existing projects, we recommend you to create migration on your own 

### New tests for price formatting 
- add tests for `NumberFormatHelper`
```
    /**
     * Inspired by formatCurrency() method, {@see \Shopsys\FrameworkBundle\Twig\PriceExtension}
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @return string
     */
    public function getFormattedPriceWithCurrencySymbolRoundedByCurrencyOnFrontend(Money $price): string
    {
        $firstDomainDefaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId(Domain::FIRST_DOMAIN_ID);
        $firstDomainLocale = $this->localizationHelper->getFrontendLocale();
        $currencyFormatter = $this->currencyFormatterFactory->createByLocaleAndCurrency($firstDomainLocale, $firstDomainDefaultCurrency);

        $intlCurrency = $this->intlCurrencyRepository->get($firstDomainDefaultCurrency->getCode(), $firstDomainLocale);

        $formattedPriceWithCurrencySymbol = $currencyFormatter->format(
            $this->rounding->roundPriceWithVat($price)->getAmount(),
            $intlCurrency->getCurrencyCode()
        );

        return $this->normalizeSpaces($formattedPriceWithCurrencySymbol);
    }
```
```
    /**
     * Inspired by formatCurrency() method, {@see \Shopsys\FrameworkBundle\Twig\PriceExtension}
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @return string
     */
    public function getFormattedPriceRoundedByCurrencyOnFrontend(Money $price): string
    {
        $firstDomainDefaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId(Domain::FIRST_DOMAIN_ID);
        $firstDomainLocale = $this->localizationHelper->getFrontendLocale();
        $currencyFormatter = $this->currencyFormatterFactory->createByLocaleAndCurrency($firstDomainLocale, $firstDomainDefaultCurrency);

        $intlCurrency = $this->intlCurrencyRepository->get($firstDomainDefaultCurrency->getCode(), $firstDomainLocale);

        $formattedPriceWithCurrencySymbol = $currencyFormatter->format(
            $this->rounding->roundPriceWithVat($price)->getAmount(),
            $intlCurrency->getCurrencyCode()
        );

        return $this->normalizeSpaces($formattedPriceWithCurrencySymbol);
    }
```
- add test for `CartBoxPage`
```
    /**
     * @param int $expectedCount
     * @param string $expectedPrice
     */
    public function seeCountAndPriceRoundedByCurrencyInCartBox(int $expectedCount, string $expectedPrice): void
    {
        $convertedPrice = Money::create($this->tester->getPriceWithVatConvertedToDomainDefaultCurrency($expectedPrice));
        $expectedFormattedPriceWithCurrency = $this->tester->getFormattedPriceWithCurrencySymbolRoundedByCurrencyOnFrontend($convertedPrice);
        $messageId = '{1} <strong class="cart__state">%itemsCount%</strong> item for <strong class="cart__state">%priceWithVat%</strong>|[2,Inf] <strong class="cart__state">%itemsCount%</strong> items for <strong class="cart__state">%priceWithVat%</strong>';
        $translatedMessageWithTags = tc($messageId, $expectedCount, ['%itemsCount%' => $expectedCount, '%priceWithVat%' => $expectedFormattedPriceWithCurrency], 'messages', $this->tester->getFrontendLocale());

        $this->tester->seeInCss(strip_tags($translatedMessageWithTags), '.js-cart-info');
    }
```
- add tests for `CartPage`
```
    /**
     * @param string $productName
     * @param string $price
     */
    public function assertProductPriceRoundedByCurrency($productName, $price)
    {
        $convertedPrice = $this->tester->getPriceWithVatConvertedToDomainDefaultCurrency($price);
        $formattedPriceWithCurrency = $this->tester->getFormattedPriceWithCurrencySymbolRoundedByCurrencyOnFrontend(Money::create($convertedPrice));
        $productPriceCell = $this->getProductTotalPriceCellByName($productName);
        $this->tester->seeInElement($formattedPriceWithCurrency, $productPriceCell);
    }
```
```
    /**
     * @param string $price
     */
    public function assertTotalPriceWithVatRoundedByCurrency($price)
    {
        $formattedPriceWithCurrency = $this->tester->getFormattedPriceWithCurrencySymbolRoundedByCurrencyOnFrontend(Money::create($price));
        $orderPriceCell = $this->getTotalProductsPriceCell();
        $message = t('Total price including VAT', [], 'messages', $this->tester->getFrontendLocale());
        $this->tester->seeInElement($message . ': ' . $formattedPriceWithCurrency, $orderPriceCell);
    }
```

### Deprecated functions and test
- these methods are deprecated and will be removed in the next major release
    - `CurrencyFormatterFactory::create()` use `createByLocaleAndCurrency()` instead
- these tests are deprecated and will be removed in the next major release
    - `CartBoxPage::seeCountAndPriceInCartBox()`
    - `CartPage::assertProductPrice()`
    - `CartPage::assertTotalPriceWithVat()`
    - `NumberFormatHelper::getFormattedPriceWithCurrencySymbolOnFrontend()`
    - `NumberFormatHelper::getFormattedPriceOnFrontend()`
