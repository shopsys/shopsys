<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Payment\Payment;
use App\Model\Payment\PaymentDataFactory;
use App\Model\Payment\PaymentFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportDataFactory;
use App\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class AuthenticatedCartModificationsResultTest extends GraphQlWithLoginTestCase
{
    private Product $testingProduct;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    /**
     * @inject
     */
    private TransportFacade $transportFacade;

    /**
     * @inject
     */
    private PaymentFacade $paymentFacade;

    /**
     * @inject
     */
    private TransportDataFactory $transportDataFactory;

    /**
     * @inject
     */
    private StoreFacade $storeFacade;

    /**
     * @inject
     */
    private PaymentDataFactory $paymentDataFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);
    }

    public function testModificationTriggeredInAddToCartMutation(): void
    {
        $productQuantity = 2;
        $this->addTestingProductToNewCart($productQuantity);

        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 72, Product::class);

        $this->hideTestingProduct();

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $secondProduct->getUuid(),
            'quantity' => $productQuantity,
        ]);

        $modifications = $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart']['modifications'];

        self::assertNotEmpty($modifications['itemModifications']['noLongerListableCartItems']);
    }

    public function testModificationTriggeredInRemoveFromCartMutation(): void
    {
        $productQuantity = 2;
        $this->addTestingProductToNewCart($productQuantity);

        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 72, Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $secondProduct->getUuid(),
            'quantity' => $productQuantity,
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'AddToCart');
        $cartItemUuid = $data['cart']['items'][1]['uuid'];

        // product has to be refreshed to prevent Doctrine from trying to flush not-persisted entity Vat
        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);

        $this->hideTestingProduct();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveFromCart.graphql', [
            'cartItemUuid' => $cartItemUuid,
        ]);
        $modifications = $this->getResponseDataForGraphQlType($response, 'RemoveFromCart')['modifications'];

        self::assertNotEmpty($modifications['itemModifications']['noLongerListableCartItems']);
    }

    public function testNoLongerListableCartItemIsReported(): void
    {
        $productQuantity = 2;
        $this->addTestingProductToNewCart($productQuantity);

        $this->hideTestingProduct();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetCart.graphql');
        $data = $this->getResponseDataForGraphQlType($response, 'cart');
        $itemModifications = $data['modifications']['itemModifications'];

        self::assertNotEmpty($itemModifications['noLongerListableCartItems']);
        self::assertEquals($this->testingProduct->getUuid(), $itemModifications['noLongerListableCartItems'][0]['product']['uuid']);

        self::assertEmpty($itemModifications['noLongerAvailableCartItemsDueToQuantity']);
        self::assertEmpty($itemModifications['cartItemsWithModifiedPrice']);
        self::assertEmpty($itemModifications['cartItemsWithChangedQuantity']);
    }

    public function testCartItemWithModifiedPriceIsReported(): void
    {
        $productQuantity = 2;
        $this->addTestingProductToNewCart($productQuantity);

        $this->modifyPriceOfTestingProduct();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetCart.graphql');
        $data = $this->getResponseDataForGraphQlType($response, 'cart');
        $itemModifications = $data['modifications']['itemModifications'];

        self::assertNotEmpty($itemModifications['cartItemsWithModifiedPrice']);
        self::assertEquals($this->testingProduct->getUuid(), $itemModifications['cartItemsWithModifiedPrice'][0]['product']['uuid']);

        self::assertEmpty($itemModifications['noLongerListableCartItems']);
        self::assertEmpty($itemModifications['noLongerAvailableCartItemsDueToQuantity']);
        self::assertEmpty($itemModifications['cartItemsWithChangedQuantity']);
    }

    public function testCartItemWithChangedQuantityIsReported(): void
    {
        $productQuantity = 2;
        $this->addTestingProductToNewCart($productQuantity);

        $this->setOneItemLeftOnStockForTestingProduct();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetCart.graphql');
        $data = $this->getResponseDataForGraphQlType($response, 'cart');
        $itemModifications = $data['modifications']['itemModifications'];

        self::assertNotEmpty($itemModifications['cartItemsWithChangedQuantity']);
        self::assertEquals($this->testingProduct->getUuid(), $itemModifications['cartItemsWithChangedQuantity'][0]['product']['uuid']);

        self::assertEmpty($itemModifications['noLongerListableCartItems']);
        self::assertEmpty($itemModifications['noLongerAvailableCartItemsDueToQuantity']);
        self::assertEmpty($itemModifications['cartItemsWithModifiedPrice']);
    }

    public function testNoLongerAvailableCartItemDueToQuantityIsReported(): void
    {
        $productQuantity = 2;
        $this->addTestingProductToNewCart($productQuantity);

        $this->setNoItemLeftOnStockForTestingProduct();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetCart.graphql');
        $data = $this->getResponseDataForGraphQlType($response, 'cart');
        $itemModifications = $data['modifications']['itemModifications'];

        self::assertNotEmpty($itemModifications['noLongerAvailableCartItemsDueToQuantity']);
        self::assertEquals($this->testingProduct->getUuid(), $itemModifications['noLongerAvailableCartItemsDueToQuantity'][0]['product']['uuid']);

        self::assertEmpty($itemModifications['noLongerListableCartItems']);
        self::assertEmpty($itemModifications['cartItemsWithModifiedPrice']);
        self::assertEmpty($itemModifications['cartItemsWithChangedQuantity']);
    }

    public function testTransportWithModifiedPriceIsReported(): void
    {
        $this->addTestingProductToNewCart(1);
        $referenceName = TransportDataFixture::TRANSPORT_PPL;
        $transport = $this->getReference($referenceName, Transport::class);
        $this->addTransportToExistingCart($transport);
        $this->changeTransportPrice($referenceName);

        $transportModifications = $this->getTransportModificationsForCartQuery();
        self::assertTrue($transportModifications['transportPriceChanged']);
    }

    public function testTransportWithNotExistingPersonalPickupStoreIsReported(): void
    {
        $this->addTestingProductToNewCart(1);
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL, Transport::class);
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1, Store::class);
        $this->addTransportToExistingCart($transport, $store->getUuid());
        $this->storeFacade->delete($store->getId());

        $transportModifications = $this->getTransportModificationsForCartQuery();
        self::assertTrue($transportModifications['personalPickupStoreUnavailable']);
    }

    public function testValidPersonalPickupStoreIsNotReported(): void
    {
        $this->addTestingProductToNewCart(1);
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL, Transport::class);

        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1, Store::class);
        $this->addTransportToExistingCart($transport, $store->getUuid());

        $transportModifications = $this->getTransportModificationsForCartQuery();
        self::assertFalse($transportModifications['personalPickupStoreUnavailable']);
    }

    public function testDeletedTransportIsReportedAsUnavailable(): void
    {
        $this->addTestingProductToNewCart(1);
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL, Transport::class);
        $this->addTransportToExistingCart($transport);
        $this->transportFacade->deleteById($transport->getId());

        $transportModifications = $this->getTransportModificationsForCartQuery();
        self::assertTrue($transportModifications['transportUnavailable']);
    }

    public function testExcludedTransportIsReportedAsUnavailable(): void
    {
        $this->addTestingProductToNewCart(1);
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL, Transport::class);
        $this->addTransportToExistingCart($transport);
        $this->setTransportAsExcludedForTestingProduct($transport);

        $transportModifications = $this->getTransportModificationsForCartQuery();
        self::assertTrue($transportModifications['transportUnavailable']);
    }

    public function testHiddenTransportIsReportedAsUnavailable(): void
    {
        $this->addTestingProductToNewCart(1);
        $referenceName = TransportDataFixture::TRANSPORT_PPL;
        $transport = $this->getReference($referenceName, Transport::class);
        $this->addTransportToExistingCart($transport);
        $this->hideTransport($referenceName);

        $transportModifications = $this->getTransportModificationsForCartQuery();
        self::assertTrue($transportModifications['transportUnavailable']);
    }

    public function testTransportWithExceededWeightLimitIsReported(): void
    {
        $this->addTestingProductToNewCart(1);
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST, Transport::class);
        $this->addTransportToExistingCart($transport);

        $transportModifications = $this->getTransportModificationsForCartQuery();
        self::assertFalse($transportModifications['transportWeightLimitExceeded']);

        $transportModifications = $this->addTestingProductToExistingCartAndGetTransportModifications(3);
        self::assertTrue($transportModifications['transportWeightLimitExceeded']);
    }

    public function testPaymentWithModifiedPriceIsReported(): void
    {
        $this->addTestingProductToNewCart(1);
        $referenceName = PaymentDataFixture::PAYMENT_CARD;
        $payment = $this->getReference($referenceName, Payment::class);
        $this->addPaymentToExistingCart($payment);
        $this->changePaymentPrice($referenceName);

        $paymentModifications = $this->getPaymentModifications();
        self::assertTrue($paymentModifications['paymentPriceChanged']);
    }

    public function testUnavailablePaymentIsReported(): void
    {
        $this->addTestingProductToNewCart(1);
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD, Payment::class);
        $this->addPaymentToExistingCart($payment);
        $this->paymentFacade->deleteById($payment->getId());

        $paymentModifications = $this->getPaymentModifications();
        self::assertTrue($paymentModifications['paymentUnavailable']);
    }

    /**
     * @param int $productQuantity
     * @return array
     */
    private function addTestingProductToNewCart(int $productQuantity): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $this->testingProduct->getUuid(),
            'quantity' => $productQuantity,
        ]);

        // product has to be refreshed to prevent Doctrine from trying to flush not-persisted entity Vat
        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);

        return $this->getResponseDataForGraphQlType($response, 'AddToCart');
    }

    private function hideTestingProduct(): void
    {
        $productData = $this->productDataFactory->createFromProduct($this->testingProduct);
        $productData->sellingDenied = true;

        $this->productFacade->edit($this->testingProduct->getId(), $productData);
        $this->handleDispatchedRecalculationMessages();
    }

    private function modifyPriceOfTestingProduct(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade */
        $pricingGroupFacade = self::getContainer()->get(PricingGroupFacade::class);

        $productData = $this->productDataFactory->createFromProduct($this->testingProduct);

        foreach ($pricingGroupFacade->getAll() as $pricingGroup) {
            $productData->productInputPricesByDomain[$pricingGroup->getDomainId()]->manualInputPricesByPricingGroupId[$pricingGroup->getId()] = Money::create(1);
        }

        $this->productFacade->edit($this->testingProduct->getId(), $productData);
        $this->handleDispatchedRecalculationMessages();
    }

    private function setOneItemLeftOnStockForTestingProduct(): void
    {
        $productData = $this->productDataFactory->createFromProduct($this->testingProduct);

        foreach ($productData->productStockData as $productStockData) {
            $productStockData->productQuantity = 0;
        }

        $productData->productStockData[1]->productQuantity = 1;

        $this->productFacade->edit($this->testingProduct->getId(), $productData);
        $this->handleDispatchedRecalculationMessages();
    }

    private function setNoItemLeftOnStockForTestingProduct(): void
    {
        $productData = $this->productDataFactory->createFromProduct($this->testingProduct);

        foreach ($productData->productStockData as $productStockData) {
            $productStockData->productQuantity = 0;
        }

        $this->productFacade->editProductStockRelation($productData, $this->testingProduct);
        $this->em->clear();
    }

    /**
     * @param int $productQuantity
     * @return array
     */
    private function addTestingProductToExistingCartAndGetTransportModifications(int $productQuantity): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $this->testingProduct->getUuid(),
            'quantity' => $productQuantity,
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'AddToCart');

        return $data['cart']['modifications']['transportModifications'];
    }

    /**
     * @return array
     */
    private function getTransportModificationsForCartQuery(): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetCart.graphql');
        $data = $this->getResponseDataForGraphQlType($response, 'cart');

        return $data['modifications']['transportModifications'];
    }

    /**
     * @return array
     */
    private function getPaymentModifications(): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetCart.graphql');
        $data = $this->getResponseDataForGraphQlType($response, 'cart');

        return $data['modifications']['paymentModifications'];
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param string|null $pickupPlaceIdentifier
     */
    private function addTransportToExistingCart(Transport $transport, ?string $pickupPlaceIdentifier = null): void
    {
        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'transportUuid' => $transport->getUuid(),
            'pickupPlaceIdentifier' => $pickupPlaceIdentifier,
        ]);
    }

    /**
     * @param string $transportReferenceName
     */
    private function changeTransportPrice(string $transportReferenceName): void
    {
        // refresh transport, so we're able to work with it as with an entity
        $transport = $this->getReference($transportReferenceName, Transport::class);
        $transportData = $this->transportDataFactory->createFromTransport($transport);
        $transportData->inputPricesByDomain[1]->pricesWithLimits[0]->price = $transport->getLowestPriceOnDomain(1)->getPrice()->add(Money::create(10));
        $this->transportFacade->edit($transport, $transportData);
    }

    /**
     * @param string $transportReferenceName
     */
    private function hideTransport(string $transportReferenceName): void
    {
        // refresh transport, so we're able to work with it as with an entity
        $transport = $this->getReference($transportReferenceName, Transport::class);
        $transportData = $this->transportDataFactory->createFromTransport($transport);
        $transportData->hidden = true;
        $this->transportFacade->edit($transport, $transportData);
    }

    /**
     * @param \App\Model\Payment\Payment $payment
     */
    private function addPaymentToExistingCart(Payment $payment): void
    {
        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangePaymentInCartMutation.graphql', [
            'paymentUuid' => $payment->getUuid(),
        ]);
    }

    /**
     * @param string $paymentReferenceName
     */
    private function changePaymentPrice(string $paymentReferenceName): void
    {
        $payment = $this->getReference($paymentReferenceName, Payment::class);
        $paymentData = $this->paymentDataFactory->createFromPayment($payment);
        $paymentData->pricesIndexedByDomainId[1] = $payment->getPrice(1)->getPrice()->add(Money::create(10));
        $this->paymentFacade->edit($payment, $paymentData);
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     */
    private function setTransportAsExcludedForTestingProduct(Transport $transport): void
    {
        // refresh testing product
        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);
        // refresh transport
        $transport = $this->transportFacade->getById($transport->getId());

        $productData = $this->productDataFactory->createFromProduct($this->testingProduct);
        $productData->excludedTransports = [$transport];
        $this->productFacade->edit($this->testingProduct->getId(), $productData);
    }
}
