# Upgrade instruction for vats per domain

There is a new way for create and manage VATs on project. 
You can set VATs per domains, set default VAT per domain, set product's VAT per domains and set transport's and payment's VATs per domains.
We deleted managing transports and payments prices by currencies  with this upgrade.

## Changes in project-base

### Form types 
 - project-base/app/config/packages/twig.yml
    ```diff
          '@ShopsysFramework/Admin/Form/imageuploadFields.html.twig'
        - '@ShopsysFramework/Admin/Form/pricetableFields.html.twig'
        + '@ShopsysFramework/Admin/Form/priceandvattableByDomainsFields.html.twig'
          '@ShopsysFramework/Admin/Form/orderItems.html.twig'
    ```

### Changes in twig templates
 - project-base/src/Shopsys/ShopBundle/Resources/views/Front/Content/Cart/index.html.twig
    ```diff
        <td class="table-cart__cell table-cart__cell--price">
            - {{ cartItem.product.vat.percent|formatPercent }}
            + {{ cartItem.product.getVatForDomain(getDomain().id).percent|formatPercent }}
        </td>
   ```

### Data fixtures
 - change VAT access in `PaymentDataFixture`
 ```diff
-     $paymentData->vat = $this->getReference(VatDataFixture::VAT_ZERO);
      $this->createPayment(self::PAYMENT_CARD, $paymentData, [ 
```
```diff
-     $paymentData->vat = $this->getReference(VatDataFixture::VAT_HIGH);
      $this->createPayment(self::PAYMENT_CASH_ON_DELIVERY, $paymentData, [TransportDataFixture::TRANSPORT_CZECH_POST]); 
```
```diff
-     $paymentData->vat = $this->getReference(VatDataFixture::VAT_HIGH);
      $this->createPayment(self::PAYMENT_CASH, $paymentData, [TransportDataFixture::TRANSPORT_PERSONAL]);
```
```diff
    foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
-       $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domain->getId());
        $price = $this->priceConverter->convertPriceWithoutVatToPriceInDomainDefaultCurrency($price, $domain->getId());
-       $paymentData->pricesByCurrencyId[$currency->getId()] = $price;
+       /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat */
+       $vat = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domain->getId());
+       $paymentData->pricesIndexedByDomainId[$domain->getId()] = $price;
+       $paymentData->vatsIndexedByDomainId[$domain->getId()] = $vat;
    }
```
- change VAT access in `TransportDataFixture`
```diff
-       $this->setPriceForAllDomainDefaultCurrencies($transportData, Money::create('99.95'));
-       $transportData->vat = $this->getReference(VatDataFixture::VAT_HIGH);
+       $this->setPriceForAllDomains($transportData, Money::create('99.95'));
        $this->createTransport(self::TRANSPORT_CZECH_POST, $transportData);
```
```diff
-       $this->setPriceForAllDomainDefaultCurrencies($transportData, Money::create('199.95'));
-       $transportData->vat = $this->getReference(VatDataFixture::VAT_HIGH);
+       $this->setPriceForAllDomains($transportData, Money::create('199.95'));
        $this->createTransport(self::TRANSPORT_PPL, $transportData);
```
```diff
-       $this->setPriceForAllDomainDefaultCurrencies($transportData, Money::zero());
-       $transportData->vat = $this->getReference(VatDataFixture::VAT_ZERO);
+       $this->setPriceForAllDomains($transportData, Money::zero());
        $this->createTransport(self::TRANSPORT_PERSONAL, $transportData);
```
```diff
-       protected function setPriceForAllDomainDefaultCurrencies(TransportData $transportData, Money $price): void
+       protected function setPriceForAllDomains(TransportData $transportData, Money $price): void
        {
            foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
-               $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domain->getId());
                $price = $this->priceConverter->convertPriceWithoutVatToPriceInDomainDefaultCurrency($price, $domain->getId());
-               $transportData->pricesByCurrencyId[$currency->getId()] = $price;
+               /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat */
+               $vat = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domain->getId());
+               $transportData->vatsIndexedByDomainId[$domain->getId()] = $vat;
+               $transportData->pricesIndexedByDomainId[$domain->getId()] = $price;
            }
        }
```
- change VAT access in `ProductDataFixture`
```diff
    protected function setVat(ProductData $productData, ?string $vatReference): void
    {
-       /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat|null $vat */
-       $vat = $vatReference === null ? null : $this->persistentReferenceFacade->getReference($vatReference);
-       $productData->vat = $vat;
+       /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[] $productVatsIndexedByDomainId */
+       $productVatsIndexedByDomainId = [];
+       foreach ($this->domain->getAllIds() as $domainId) {
+           if ($vatReference !== null) {
+               $productVatsIndexedByDomainId[$domainId] = $this->persistentReferenceFacade->getReferenceForDomain($vatReference, Domain::FIRST_DOMAIN_ID);
+           }
+       }
+       $productData->vatsIndexedByDomainId = $productVatsIndexedByDomainId;
    }
```
- change VAT access in `VatDataFixture`
    - look at [diff](https://github.com/shopsys/shopsys/pull/1498/files#diff-e739c6a126c3dda0c19d2e5e9f493074)


### Tests
 - `CartTest`
    - look at [diff](https://github.com/shopsys/shopsys/pull/1498/files#diff-b3d3aec98c03f8f906b6d577171532f4)
 - `CartItemTest`
    - look at [diff](https://github.com/shopsys/shopsys/pull/1498/files#diff-1e11042bb981dad6ec5e6d1dbe4e4bdd)
 - `CartWatcherTest`
    - look at [diff](https://github.com/shopsys/shopsys/pull/1498/files#diff-5ae91fd6f7e81bf2306850df29cd7c56)
 - `OrderTransportAndPaymentTest`
    - look at [diff](https://github.com/shopsys/shopsys/pull/1498/files#diff-8f68f31e743777cfa8bb19a044821859)
 - `OrderPreviewCalculationTest`
    - look at [diff](https://github.com/shopsys/shopsys/pull/1498/files#diff-8d2aa0b1c594fd3351011afc0b4dda24)
 - `PaymentTest`
    - look at [diff](https://github.com/shopsys/shopsys/pull/1498/files#diff-4405041627b2b93dff459dff5a98d63c)
 - `ProductInputPriceRecalculatorTest`
    - look at [diff](https://github.com/shopsys/shopsys/pull/1498/files#diff-88dc599fdac5a36e779505c8f40d1b62)
 - `ProductDomainTest`
    - look at [diff](https://github.com/shopsys/shopsys/pull/1498/files#diff-4b7104f6ceb56f1ccfe3a0ca22554b71)
 - `ProductFacadeTest`
    - look at [diff](https://github.com/shopsys/shopsys/pull/1498/files#diff-37111ac4ea6bcb53bb152c7426941295)
 - `ProductVariantCreationTest`
    - look at [diff](https://github.com/shopsys/shopsys/pull/1498/files#diff-44faf12e003acef622cfb31ad4f25e98)
 - `ProductVisibilityRepositoryTest`
    - look at [diff](https://github.com/shopsys/shopsys/pull/1498/files#diff-dc2c542c2ef2ee7a82a43f14f1a0fdd0)
 - `VatFacadeTest`
    - look at [diff](https://github.com/shopsys/shopsys/pull/1498/files#diff-7846a28218f377d20e7d550c362b43d2)
 
#### Smoke test
 - project-base/tests/ShopBundle/Smoke/Http/RouteConfigCustomization.php
 ```diff
    /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat */
-   $vat = $this->getPersistentReference(VatDataFixture::VAT_SECOND_LOW);
+   $vat = $this->getPersistentReference(VatDataFixture::VAT_SECOND_LOW, Domain::FIRST_DOMAIN_ID);
    /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $newVat */
-   $newVat = $this->getPersistentReference(VatDataFixture::VAT_LOW);
+   $newVat = $this->getPersistentReference(VatDataFixture::VAT_LOW, Domain::FIRST_DOMAIN_ID); 
```
 - project-base/tests/ShopBundle/Smoke/NewProductTest.php
    - look at [diff](https://github.com/shopsys/shopsys/pull/1498/files#diff-f96ae419b9841369f8e31b8921b9af6d)

 ## Following methods have changed their signatures:
```diff
- VatController::__construct(VatFacade $vatFacade, PricingSetting $pricingSetting, VatInlineEdit $vatInlineEdit, ConfirmDeleteResponseFactory $confirmDeleteResponseFactory)
+ VatController::__construct(VatFacade $vatFacade, PricingSetting $pricingSetting, VatInlineEdit $vatInlineEdit, ConfirmDeleteResponseFactory $confirmDeleteResponseFactory, AdminDomainTabsFacade $adminDomainTabsFacade)
```
```diff
- PaymentFormType::__construct(TransportFacade $transportFacade, VatFacade $vatFacade, CurrencyFacade $currencyFacade, PaymentFacade $paymentFacade)
+ PaymentFormType::__construct(TransportFacade $transportFacade, PaymentFacade $paymentFacade)
```
```diff
- TransportFormType::__construct(VatFacade $vatFacade, PaymentFacade $paymentFacade, CurrencyFacade $currencyFacade, TransportFacade $transportFacade)
+ TransportFormType::__construct(PaymentFacade $paymentFacade, TransportFacade $transportFacade)
```
```diff
- VatSettingsFormType::__construct(VatFacade $vatFacade)
+ VatSettingsFormType::__construct(VatFacade $vatFacade, AdminDomainTabsFacade $adminDomainTabsFacade)
```
```diff
- OrderItemPriceCalculation::calculatePriceWithoutVat(OrderItemData $orderItemData): Money
+ OrderItemPriceCalculation::calculatePriceWithoutVat(OrderItemData $orderItemData, int $domainId): Money
```
```diff
- OrderFacade::calculateOrderItemDataPrices(OrderItemData $orderItemData): void
+ OrderFacade::calculateOrderItemDataPrices(OrderItemData $orderItemData, int $domainId): void
```
```diff
- PaymentGridFactory::__construct(GridFactory $gridFactory, PaymentRepository $paymentRepository, Localization $localization, PaymentFacade $paymentFacade)
+ PaymentGridFactory::__construct(GridFactory $gridFactory, PaymentRepository $paymentRepository, Localization $localization, PaymentFacade $paymentFacade, AdminDomainTabsFacade $adminDomainTabsFacade, CurrencyFacade $currencyFacade)
```
```diff
- Payment::setPrice(PaymentPriceFactoryInterface $paymentPriceFactory, Currency $currency, Money $price)
+ Payment::setPrice(Money $price, int $domainId): void
```
```diff
- Payment::getPrice(Currency $currency)
+ Payment::getPrice(int $domainId): PaymentPrice
```
```diff
- PaymentDomain::__construct(Payment $payment, $domainId)
+ PaymentDomain::__construct(Payment $payment, int $domainId, Vat $vat)
```
```diff
- PaymentFacade::updatePaymentPrices(Payment $payment, $pricesByCurrencyId)
+ PaymentFacade::updatePaymentPrices(Payment $payment, array $pricesIndexedByDomainId, array $vatsIndexedByDomainId): void
```
```diff
- PaymentPrice::__construct(Payment $payment, Currency $currency, Money $price)
+ PaymentPrice::__construct(Payment $payment, Money $price, int $domainId)
```
```diff
- PaymentPriceCalculation::calculateIndependentPrice(Payment $payment, Currency $currency): Price
+ PaymentPriceCalculation::calculateIndependentPrice(?Payment $payment, Currency $currency, int $domainId): Price
```
```diff
- PaymentPriceFactory::create(Payment $payment, Currency $currency, Money $price): PaymentPrice
+ PaymentPriceFactory::create(Payment $payment, Money $price, int $domainId): PaymentPrice
```
```diff
- PaymentPriceFactoryInterface::create(Payment $payment, Currency $currency, Money $price): PaymentPrice
+ PaymentPriceFactoryInterface::create(Payment $payment, Money $price, int $domainId): PaymentPrice
```
```diff
- CurrencyFacade::__construct(TransportRepository $transportRepository, PaymentPriceFactoryInterface $paymentPriceFactory, TransportPriceFactoryInterface $transportPriceFactory, CurrencyFactoryInterface $currencyFactory)
+ CurrencyFacade::__construct(TransportRepository $transportRepository, PaymentPriceFactoryInterface $paymentPriceFactory, TransportPriceFactoryInterface $transportPriceFactory, CurrencyFactoryInterface $currencyFactory, VatFacade $vatFacade)
```
```diff
- InputPriceRecalculator::__construct(EntityManagerInterface $em, InputPriceCalculation $inputPriceCalculation, PaymentPriceCalculation $paymentPriceCalculation, TransportPriceCalculation $transportPriceCalculation)
+ InputPriceRecalculator::__construct(EntityManagerInterface $em, InputPriceCalculation $inputPriceCalculation, PaymentPriceCalculation $paymentPriceCalculation, TransportPriceCalculation $transportPriceCalculation, CurrencyFacade $currencyFacade)
```
```diff
- Vat::__construct(VatData $vatData)
+ Vat::__construct(VatData $vatData, int $domainId)
```
```diff
- VatFacade::__construct(EntityManagerInterface $em, VatRepository $vatRepository, Setting $setting, ProductPriceRecalculationScheduler $productPriceRecalculationScheduler, VatFactoryInterface $vatFactory)
+ VatFacade::__construct(EntityManagerInterface $em, VatRepository $vatRepository, Setting $setting, ProductPriceRecalculationScheduler $productPriceRecalculationScheduler, VatFactoryInterface $vatFactory, Domain $domain)
```
```diff
- VatFacade::create(VatData $vatData)
+ VatFacade::create(VatData $vatData, int $domainId): Vat
```
```diff
- VatFactory::create(VatData $data): Vat
+ VatFactory::create(VatData $data, int $domainId): Vat
```
```diff
- VatFactoryInterface::create(VatData $data): Vat
+ VatFactoryInterface::create(VatData $data, int $domainId): Vat
```
```diff
- VatGridFactory::__construct(EntityManagerInterface $em, GridFactory $gridFactory, VatFacade $vatFacade, PriceCalculation $priceCalculation)
+ VatGridFactory::__construct(EntityManagerInterface $em, GridFactory $gridFactory, VatFacade $vatFacade, PriceCalculation $priceCalculation, AdminDomainTabsFacade $adminDomainTabsFacade)
```
```diff
- VatInlineEdit::__construct(VatGridFactory $vatGridFactory, VatFacade $vatFacade, FormFactoryInterface $formFactory, VatDataFactoryInterface $vatDataFactory)
+ VatInlineEdit::__construct(VatGridFactory $vatGridFactory, VatFacade $vatFacade, FormFactoryInterface $formFactory, VatDataFactoryInterface $vatDataFactory, AdminDomainTabsFacade $adminDomainTabsFacade)
```
```diff
- TransportGridFactory::__construct(GridFactory $gridFactory, TransportRepository $transportRepository, Localization $localization, TransportFacade $transportFacade)
+ TransportGridFactory::__construct(GridFactory $gridFactory, TransportRepository $transportRepository, Localization $localization, TransportFacade $transportFacade, AdminDomainTabsFacade $adminDomainTabsFacade)
```
```diff
- Transport::getPrice(Currency $currency)
+ Transport::getPrice(int $domainId): TransportPrice
```
```diff
- Transport::setPrice(TransportPriceFactoryInterface $transportPriceFactory, Currency $currency, Money $price)
+ Transport::setPrice(Money $price, int $domainId): void
```
```diff
- protected Transport::getTransportDomain(int $domainId)
+ public Transport::getTransportDomain(int $domainId)
```
```diff
- TransportDomain::__construct(Transport $transport, $domainId)
+ TransportDomain::__construct(Transport $transport, int $domainId, Vat $vat)
```
```diff
- TransportFacade::updateTransportPrices(Transport $transport, array $pricesByCurrencyId)
+ TransportFacade::updateTransportPrices(Transport $transport, array $pricesIndexedByDomainId): void
```
```diff
- TransportPrice::__construct(Transport $transport, Currency $currency, Money $price)
+ TransportPrice::__construct(Transport $transport, Money $price, int $domainId)
```
```diff
- TransportPriceCalculation::calculateIndependentPrice(Transport $transport, Currency $currency): Price
+ TransportPriceCalculation::calculateIndependentPrice(Transport $transport, Currency $currency, int $domainId): Price
```
```diff
- TransportPriceFactory::create(Transport $transport, Currency $currency, Money $price)
+ TransportPriceFactory::create(Transport $transport, Money $price, int $domainId)
```
```diff
- TransportPriceFactoryIntrface::create(Transport $transport, Currency $currency, Money $price)
+ TransportPriceFactoryInterface::create(Transport $transport, Money $price, int $domainId)
```
```diff
- DomainDataCreator::__construct(Domain $domain, Setting $setting, SettingValueRepository $settingValueRepository, MultidomainEntityDataCreator $multidomainEntityDataCreator, TranslatableEntityDataCreator $translatableEntityDataCreator, PricingGroupDataFactory $pricingGroupDataFactory, PricingGroupFacade $pricingGroupFacade)
+ DomainDataCreator::__construct(Domain $domain, Setting $setting, SettingValueRepository $settingValueRepository, MultidomainEntityDataCreator $multidomainEntityDataCreator, TranslatableEntityDataCreator $translatableEntityDataCreator, PricingGroupDataFactory $pricingGroupDataFactory, PricingGroupFacade $pricingGroupFacade, VatDataFactory $vatDataFactory, VatFacade $vatFacade)
```

## Following methods have been removed:
- `Payment::getVat()`
    - use `Payment::getPaymentDomain(int $domainId)::getVat` instead
- `PaymentFacade::getPaymentPricesWithVatIndexedByPaymentId(Currency $currency): array`
    - use `PaymentFacade::getPaymentPricesWithVatByCurrencyAndDomainIdIndexedByPaymentId(Currency $currency, int $domainId): array` instead
- `PaymentFacade::getPaymentVatPercentsIndexedByPaymentId()`
    - use `PaymentFacade::getPaymentVatPercentsByDomainIdIndexedByPaymentId(int $domainId): array` instead
- `PaymentFacade::getIndependentBasePricesIndexedByCurrencyId(Payment $payment)`
    - use `PaymentFacade::getIndependentBasePricesIndexedByDomainId(Payment $payment): array` instead
- `PaymentPrice::getCurrency()`
    - removed without replacement
- `CurrencyFacade::createTransportAndPaymentPrices(Currency $currency)`
    - removed without replacement
- `VatFacade::getAll()`
    - use `VatFacade::getAllForDomain(int $domainId): array` instead
- `VatFacade::getAllIncludingMarkedForDeletion()`
    - use `VatFacade::getAllForDomainIncludingMarkedForDeletion(int $domainId): array` instead
- `VatFacade::getDefaultVat()`
    - use `VatFacade::getDefaultVatForDomain(int $domainId): Vat` instead
- `VatFacade::setDefaultVat(Vat $vat)`
    - use `VatFacade::setDefaultVatForDomain(Vat $vat, int $domainId): void` instead
- `VatFacade::getAllExceptId($vatId)`
    - use `VatFacade::getAllForDomainExceptId(int $domainId, int $vatId)` instead
- `VatRepository::getAll()`
    - use `VatRepository::getAllForDomain(int $domainId): array` instead
- `VatRepository::getAllIncludingMarkedForDeletion()`
    - use `VatRepository::getAllForDomainIncludingMarkedForDeletion(int $domainId): array` instead
- `VatRepository::getAllExceptId($vatId)`
    - use `VatRepository::getAllForDomainExceptId(int $domainId, int $vatId): array` instead
- `Product::getVat()`
    - use `Product::getVatForDomain(int $domainId): Vat` instead
- `Product::changeVat()`
    - use `Product::changeVatForDomain(Vat $vat, int $domainId)` instead
- `Transport::getVat()`
    - use `Transport::getTransportDomain(int $domainId)::getVat()` instead 
- `TransportFacade::getTransportPricesWithVatIndexedByTransportId(Currency $currency): array`
    - use `TransportFacade::getTransportPricesWithVatByCurrencyAndDomainIdIndexedByTransportId(Currency $currency, int $domainId): array` instead
- `TransportFacade::getTransportVatPercentsIndexedByTransportId()`
    - use `TransportFacade::getTransportVatPercentsByDomainIdIndexedByTransportId(int $domainId): array` instead
- `TransportFacade::getIndependentBasePricesIndexedByCurrencyId(Transport $transport)`
    - use `TransportFacade::getIndependentBasePricesIndexedByDomainId(Transport $transport): array` instead
- `TransportPrice::getCurrency(): Currency`
    - removed without replacement

## Following classes have been removed:
- `PriceTableType`
    - use `PriceAndVatTableByDomainsType`
