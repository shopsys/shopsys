# Upgrade Order Processing

## General changes that have to be known

This article covers the changes to the order processing system implemented in the latest release.
These changes aim to enhance functionality, improve maintainability, and make implementing the changes easier.

### What has changed?

Introducing new middleware components to the order processing system instead of previously used order preview calculation.
The main idea of a new processing system is to fill the `OrderData` object with all necessary data for the order creation, so the order creation itself is just a matter of use the `OrderData` to create the appropriate entity.

The new order processing system is based on the middleware pattern.
Each middleware is responsible for filling the `OrderData` object with a specific part of the order data.
The order of the middleware is important, as each middleware can depend on the data filled by the previous middleware.

You can configure the order of middlewares and which are used in the `app/config/packages/shopsys_framework.yaml` file.

To upgrade fully to the new order processing system, you need to implement your own business logic into the middlewares and register them in the previously mentioned file.

It's possible that your business logic is truly complex, and you may question the benefits of the new system.

In that case, you may:

-   Use the old order preview calculation (chances are you already have overloaded quite a portion of OrderPreview and the OrderPreviewCalculation classes).
    You need to copy the old classes to your project and the order process will work as before.
    But keep in mind that the OrderPreview system may introduce some problems during future upgrades.

-   You may create a new middleware that will leverage the old order preview calculation system.
    This way, you can keep the old system and still benefit from the new middleware system.
    This may be a good way to gradually migrate to the new system.

For more information about the order processing system, you may see the [Order Processing](https://docs.shopsys.com/introduction/order-processing/) article and the [Adding a New Order Item Type](https://docs.shopsys.com/cookbook/adding-a-new-order-item-type) cookbook.

## BC Breaking changes

-   The method `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade::__construct()` was changed:

```diff
 public function __construct(
     // ...
     protected readonly Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade,
+    protected readonly Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactory $deliveryAddressFactory,
+    protected readonly Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory,
 )
```

-   `Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade`:
    -   The method `addSubscribedEmail()` was removed, use `addSubscribedEmailIfNotExists()` instead.
    -   The method `getNewsletterSubscriberById()` is now strictly typed.
    -   The method `deleteById()` was removed, use `delete()` instead.
-   `Shopsys\FrameworkBundle\Model\Newsletter\NewsletterRepository`:
    -   The method `existsSubscribedEmail()` is now strictly typed.
    -   The method `getNewsletterSubscriberById()` was removed, use `findNewsletterSubscriberById()` instead.
-   `Shopsys\FrameworkBundle\Model\Order\Item\OrderItem`:
    -   The method `getPriceWithoutVat()` was removed, use `getPrice()` instead.
    -   The method `getPriceWithVat()` was removed, use `getPrice()` instead.
    -   Property `$priceWithoutVat` was removed, use `$unitPriceWithoutVat` instead.
    -   Property `$priceWithVat` was removed, use `unitPriceWithVat` instead.
    -   Constant `TYPE_PAYMENT` was removed use `OrderItemTypeEnum::TYPE_PAYMENT` instead.
    -   Constant `TYPE_PRODUCT` was removed use `OrderItemTypeEnum::TYPE_PRODUCT` instead.
    -   Constant `TYPE_TRANSPORT` was removed use `OrderItemTypeEnum::TYPE_TRANSPORT` instead.
    -   Constant `TYPE_DISCOUNT` was removed use `OrderItemTypeEnum::TYPE_DISCOUNT` instead.
    -   Constant `TYPE_ROUNDING` was removed use `OrderItemTypeEnum::TYPE_ROUNDING` instead.
-   `Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory`:

    -   The method `Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory::create()` was changed:

    ```diff
     public function create(
    +    string $orderItemType,
     ): Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData
    ```

-   The method `Shopsys\FrameworkBundle\Model\Order\Order::setTotalPrice()` was removed, use `setTotalPrices()` instead.
-   The method `Shopsys\FrameworkBundle\Model\Order\OrderDataFactory::__construct()` was changed:

```diff
 public function __construct(
     protected readonly Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory,
     protected readonly Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundDataFactory $paymentTransactionRefundDataFactory,
+    protected readonly Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum $orderItemTypeEnum,
 )
```

-   The property `Shopsys\FrameworkBundle\Model\Order\OrderData::$itemsWithoutTransportAndPayment` was removed.

-   `Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData`:

    -   Property `$priceWithoutVat` was removed, use `$unitPriceWithoutVat` instead.
    -   Property `$priceWithVat` was removed, use `$unitPriceWithVat` instead.

-   `Shopsys\FrameworkBundle\Model\Order\OrderFacade`:

    -   The method `__construct()` was changed:

    ```diff
     public function __construct(
         // ...
         protected readonly Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser,
    -    protected readonly Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory,
         protected readonly Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade $heurekaFacade,
         // ...
     )
    ```

    -   The method `createOrder()` was removed, use `Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade::placeOrder()` instead.
    -   The method `setOrderDataAdministrator()` was removed, the purpose now serves the `SetOrderDataAsAdministratorMiddleware`.
    -   The method `getOrdersCountByEmailAndDomainId()` was removed, no replacement suggested.
    -   The method `fillOrderItems()` was removed, use `Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade::fillOrderItems()` instead.
    -   The method `fillOrderProducts()` was removed, use `createSpecificOrderItem()` and `fillOrderItems()` methods from `Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade` class.
    -   The method `fillOrderPayment()` was removed, use `createSpecificOrderItem()` and `fillOrderItems()` methods from `Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade` class.
    -   The method `fillOrderTransport()` was removed, use `createSpecificOrderItem()` and `fillOrderItems()` methods from `Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade` class.
    -   The method `fillOrderRounding()` was removed, use `createSpecificOrderItem()` and `fillOrderItems()` methods from `Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade` class.
    -   The method `addOrderItemDiscount()` was removed, use `createSpecificOrderItem()` and `fillOrderItems()` methods from `Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade` class.
    -   The method `updateOrderDataWithDeliveryAddress()` was removed, the purpose now serves the `SetDeliveryAddressByDeliveryAddressUuidMiddleware`.

-   The method `Shopsys\FrameworkBundle\Model\Order\OrderRepository::getOrdersCountByEmailAndDomainId()` was removed, no replacement suggested.
-   The class `Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview` was removed, use new `OrderProcessor` instead.
-   The class `Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewCalculation` was removed, use new `OrderProcessor` instead.
-   The class `Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory` was removed, use new `OrderProcessor` instead.
-   `Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade`:

    -   The method `__construct()` was changed:

    ```diff
     public function __construct(
         protected readonly Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade,
         protected readonly Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository $promoCodeProductRepository,
         protected readonly Shopsys\FrameworkBundle\Component\Domain\Domain $domain,
         protected readonly Shopsys\FrameworkBundle\Model\Order\PromoCode\ProductPromoCodeFiller $productPromoCodeFiller,
    -    protected readonly Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitResolver $promoCodeLimitByCartTotalResolver,
         protected readonly Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser,
         protected readonly Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupRepository $promoCodePricingGroupRepository,
    +    protected readonly Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeApplicableProductsTotalPriceCalculator $promoCodeApplicableProductsTotalPriceCalculator,
     )
    ```

    -   The method `validatePromoCodeByProductsInCart()` was changed:

    ```diff
     protected function validatePromoCodeByProductsInCart(
         Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode,
    -    Shopsys\FrameworkBundle\Model\Cart\Cart $cart,
    +    array $products,
    -)
    +): array
    ```

    -   The method `validateLimit()` was changed:

    ```diff
     protected function validateLimit(
         Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode,
    -    Shopsys\FrameworkBundle\Model\Cart\Cart $cart,
    +    Shopsys\FrameworkBundle\Model\Pricing\Price $price,
     ): void
    ```

    -   The method `validatePromoCodeByFlags()` was changed:

    ```diff
     protected function validatePromoCodeByFlags(
         Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode,
    -    Shopsys\FrameworkBundle\Model\Cart\Cart $cart,
    +    array $products,
    -): void
    +): array
    ```

-   `Shopsys\FrameworkBundle\Model\Order\PromoCode\ProductPromoCodeFiller`

    -   The method `__construct()` was changed:

    ```diff
     public function __construct(
    -    protected readonly Shopsys\FrameworkBundle\Component\Domain\Domain $domain,
         protected readonly Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository $promoCodeProductRepository,
         protected readonly Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryRepository $promoCodeCategoryRepository,
         protected readonly Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandRepository $promoCodeBrandRepository,
         protected readonly Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository $promoCodeFlagRepository,
     )
    ```

    -   The method `fillPromoCodeDiscountsForAllProducts()` was changed:

    ```diff
     protected function fillPromoCodeDiscountsForAllProducts(
         array $quantifiedProducts,
         Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $validEnteredPromoCode,
    +    int $domainId,
     ): array
    ```

    -   The method `fillPromoCodes()` was changed:

    ```diff
     protected function fillPromoCodes(
         array $quantifiedProducts,
         array $allowedProductIds,
         Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $validEnteredPromoCode,
    +    int $domainId,
     ): array
    ```

    -   The method `filterProductByPromoCodeFlags()` was changed:

    ```diff
     public function filterProductByPromoCodeFlags(
         Shopsys\FrameworkBundle\Model\Product\Product $product,
         Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $validEnteredPromoCode,
    +    int $domainId,
     ): Shopsys\FrameworkBundle\Model\Product\Product|null
    ```

-   The method `Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitRepository::getHighestLimitByPromoCodeAndTotalPrice()` was changed:

```diff
 public function getHighestLimitByPromoCodeAndTotalPrice(
     Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode,
-    string $price,
+    Shopsys\FrameworkBundle\Component\Money\Money $totalPriceAmountWithVat,
 ): Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit|null
```

-   The class `Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitResolver` was removed, use `Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade::getHighestLimitByPromoCodeAndTotalPrice()` instead.
-   The class `Shopsys\FrameworkBundle\Model\Order\Watcher\TransportAndPaymentWatcher` was removed, no replacement suggested.
-   The class `Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductDiscountCalculation` was removed, use `Shopsys\FrameworkBundle\Model\Order\PromoCode\DiscountCalculation` instead.
-   The method `Shopsys\FrontendApiBundle\Model\Cart\Payment\CartPaymentDataFactory::__construct()` was changed:

```diff
 public function __construct(
     protected readonly Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade,
     protected readonly Shopsys\FrameworkBundle\Component\Domain\Domain $domain,
-    protected readonly Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser,
-    protected readonly Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade,
-    protected readonly Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory,
-    protected readonly Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation,
+    protected readonly Shopsys\FrameworkBundle\Model\Cart\CartPriceProvider $cartPriceProvider,
 )
```

-   The method `Shopsys\FrontendApiBundle\Model\Cart\Transport\CartTransportDataFactory::__construct()` was changed:

```diff
 public function __construct(
     protected readonly Shopsys\FrameworkBundle\Component\Domain\Domain $domain,
     protected readonly Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade,
-    protected readonly Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser,
-    protected readonly Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade,
-    protected readonly Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory,
-    protected readonly Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation,
+    protected readonly Shopsys\FrameworkBundle\Model\Cart\CartPriceProvider $cartPriceProvider,
 )
```

-   The method `Shopsys\FrontendApiBundle\Model\Cart\TransportAndPaymentWatcherFacade::__construct()` was changed:

```diff
 public function __construct(
-    protected readonly Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade,
     protected readonly Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade,
     protected readonly Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade,
-    protected readonly Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory,
     protected readonly Shopsys\FrameworkBundle\Component\Domain\Domain $domain,
-    protected readonly Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser,
     protected readonly Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade,
     protected readonly Shopsys\FrontendApiBundle\Model\Cart\Transport\CartTransportFacade $cartTransportFacade,
     protected readonly Shopsys\FrontendApiBundle\Model\Transport\TransportValidationFacade $transportValidationFacade,
     protected readonly Shopsys\FrontendApiBundle\Model\Cart\Payment\CartPaymentFacade $cartPaymentFacade,
     protected readonly Shopsys\FrontendApiBundle\Model\Payment\PaymentValidationFacade $paymentValidationFacade,
+    protected readonly Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor $orderProcessor,
+    protected readonly Shopsys\FrameworkBundle\Model\Order\OrderDataFactory $orderDataFactory,
+    protected readonly Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory $orderInputFactory,
 )
```

-   `Shopsys\FrontendApiBundle\Model\Order\OrderDataFactory`:
    -   The method `updateOrderDataFromCart()`, use new `OrderProcessor` to create `OrderData` properly.
    -   The method `setOrderDataByStore()` was removed, use new `OrderProcessor` to create `OrderData` properly.
    -   The method `isPickupPlaceIdentifierIntegerInString()` was removed, use new `OrderProcessor` to create `OrderData` properly.
-   The class `Shopsys\FrontendApiBundle\Model\Order\PlaceOrderFacade` was removed, use `Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade` instead.
-   The method `Shopsys\FrontendApiBundle\Model\Payment\PaymentValidationFacade::__construct()` was changed:

```diff
 public function __construct(
     protected readonly Shopsys\FrameworkBundle\Component\Domain\Domain $domain,
-    protected readonly Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade,
-    protected readonly Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory,
     protected readonly Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser,
     protected readonly Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade,
+    protected readonly Shopsys\FrameworkBundle\Model\Cart\CartPriceProvider $cartPriceProvider,
 )
```

-   The method `Shopsys\FrontendApiBundle\Model\Transport\TransportValidationFacade::__construct()` was changed:

```diff
 public function __construct(
     protected readonly Shopsys\FrameworkBundle\Model\Store\StoreFacade $storeFacade,
     protected readonly Shopsys\FrameworkBundle\Component\Domain\Domain $domain,
-    protected readonly Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade,
-    protected readonly Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory,
     protected readonly Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser,
     protected readonly Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade,
+    protected readonly Shopsys\FrameworkBundle\Model\Cart\CartPriceProvider $cartPriceProvider,
 )
```

-   `Shopsys\FrontendApiBundle\Model\Mutation\Order\CreateOrderMutation`:

    -   The method `__construct()` was changed:

    ```diff
     public function __construct(
         protected readonly OrderDataFactory $orderDataFactory,
    -    protected readonly PlaceOrderFacade $placeOrderFacade,
    -    protected readonly OrderMailFacade $orderMailFacade,
         protected readonly CurrentCustomerUser $currentCustomerUser,
         protected readonly CartApiFacade $cartApiFacade,
    -    protected readonly DeliveryAddressFacade $deliveryAddressFacade,
         protected readonly CreateOrderResultFactory $createOrderResultFactory,
         protected readonly CartWatcherFacade $cartWatcherFacade,
    +    protected readonly Domain $domain,
    +    protected readonly OrderProcessor $orderProcessor,
    +    protected readonly PlaceOrderFacade $placeOrderFacade,
    +    protected readonly OrderInputFactory $orderInputFactory,
     )
    ```

    -   The method `resolveDeliveryAddress()` was removed, use `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade::resolveDeliveryAddress()` instead.
    -   The method `sendEmail()` was removed, use `Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade::placeOrder()` to place order with email sent.

-   `Shopsys\FrontendApiBundle\Model\Resolver\Price\PriceQuery`:

    -   The method `__construct()` was changed:

    ```diff
     public function __construct(
         protected readonly ProductCachedAttributesFacade $productCachedAttributesFacade,
    -    protected readonly ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
         protected readonly PaymentPriceCalculation $paymentPriceCalculation,
         protected readonly Domain $domain,
         protected readonly CurrencyFacade $currencyFacade,
         protected readonly TransportPriceCalculation $transportPriceCalculation,
         protected readonly PriceFacade $priceFacade,
         protected readonly CurrentCustomerUser $currentCustomerUser,
    -    protected readonly OrderPreviewFactory $orderPreviewFactory,
         protected readonly CartApiFacade $cartApiFacade,
         protected readonly OrderApiFacade $orderApiFacade,
    +    protected readonly CartPriceProvider $cartPriceProvider,
     )
    ```

    -   The method `priceByProductQuery()` is now strictly typed.

-   The method `Shopsys\FrameworkBundle\Controller\Admin\NewsletterController::deleteAction()` is now strictly typed.
