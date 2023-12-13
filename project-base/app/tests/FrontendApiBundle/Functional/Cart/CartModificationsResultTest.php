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
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CartModificationsResultTest extends GraphQlTestCase
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

        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
    }

    public function testModificationTriggeredInAddToCartMutation(): void
    {
        $productQuantity = 2;
        $newlyCreatedCart = $this->addTestingProductToNewCart($productQuantity);

        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 72);

        $this->hideTestingProduct();

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => $newlyCreatedCart['uuid'],
            'productUuid' => $secondProduct->getUuid(),
            'quantity' => $productQuantity,
        ]);

        $modifications = $response['data']['AddToCart']['cart']['modifications'];

        self::assertNotEmpty($modifications['itemModifications']['noLongerListableCartItems']);
    }

    public function testModificationTriggeredInRemoveFromCartMutation(): void
    {
        $productQuantity = 2;
        $newlyCreatedCart = $this->addTestingProductToNewCart($productQuantity);

        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 72);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => $newlyCreatedCart['uuid'],
            'productUuid' => $secondProduct->getUuid(),
            'quantity' => $productQuantity,
        ]);

        $cartItemUuid = $response['data']['AddToCart']['cart']['items'][1]['uuid'];

        // product has to be refreshed to prevent Doctrine from trying to flush not-persisted entity Vat
        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $this->hideTestingProduct();

        $removeFromCartMutation = 'mutation {
            RemoveFromCart(input: {
                cartUuid: "' . $newlyCreatedCart['uuid'] . '"
                cartItemUuid: "' . $cartItemUuid . '"
            }) {
                 modifications {
                    itemModifications {
                        noLongerListableCartItems{
                            uuid
                        }
                    }
                }
            }
        }';
        $response = $this->getResponseContentForQuery($removeFromCartMutation);
        $modifications = $response['data']['RemoveFromCart']['modifications'];

        self::assertNotEmpty($modifications['itemModifications']['noLongerListableCartItems']);
    }

    public function testNoLongerListableCartItemIsReported(): void
    {
        $productQuantity = 2;
        $newlyCreatedCart = $this->addTestingProductToNewCart($productQuantity);

        $this->hideTestingProduct();

        $getCartQuery = '{
            cart(cartInput: {cartUuid: "' . $newlyCreatedCart['uuid'] . '"}) {
                modifications {
                    itemModifications {
                        noLongerListableCartItems {
                            uuid
                            product {
                                uuid
                            }
                        }
                        noLongerAvailableCartItemsDueToQuantity {
                            uuid
                        }
                        cartItemsWithModifiedPrice {
                            uuid
                        }
                        cartItemsWithChangedQuantity {
                            uuid
                        }
                    }
                }
            }
        }';

        $response = $this->getResponseContentForQuery($getCartQuery);
        $modifications = $response['data']['cart']['modifications'];
        $itemModifications = $modifications['itemModifications'];

        self::assertNotEmpty($itemModifications['noLongerListableCartItems']);
        self::assertEquals($this->testingProduct->getUuid(), $itemModifications['noLongerListableCartItems'][0]['product']['uuid']);

        self::assertEmpty($itemModifications['noLongerAvailableCartItemsDueToQuantity']);
        self::assertEmpty($itemModifications['cartItemsWithModifiedPrice']);
        self::assertEmpty($itemModifications['cartItemsWithChangedQuantity']);
    }

    public function testCartItemWithModifiedPriceIsReported(): void
    {
        $productQuantity = 2;
        $newlyCreatedCart = $this->addTestingProductToNewCart($productQuantity);

        $this->modifyPriceOfTestingProduct();

        $getCartQuery = '{
            cart(cartInput: {cartUuid: "' . $newlyCreatedCart['uuid'] . '"}) {
                modifications {
                    itemModifications {
                        cartItemsWithModifiedPrice {
                            uuid
                            product {
                                uuid
                            }
                        }
                        noLongerAvailableCartItemsDueToQuantity {
                            uuid
                        }
                        noLongerListableCartItems {
                            uuid
                        }
                        cartItemsWithChangedQuantity {
                            uuid
                        }
                    }
                }
            }
        }';

        $response = $this->getResponseContentForQuery($getCartQuery);
        $modifications = $response['data']['cart']['modifications'];
        $itemModifications = $modifications['itemModifications'];

        self::assertNotEmpty($itemModifications['cartItemsWithModifiedPrice']);
        self::assertEquals($this->testingProduct->getUuid(), $itemModifications['cartItemsWithModifiedPrice'][0]['product']['uuid']);

        self::assertEmpty($itemModifications['noLongerListableCartItems']);
        self::assertEmpty($itemModifications['noLongerAvailableCartItemsDueToQuantity']);
        self::assertEmpty($itemModifications['cartItemsWithChangedQuantity']);
    }

    public function testCartItemWithChangedQuantityIsReported(): void
    {
        $productQuantity = 2;
        $newlyCreatedCart = $this->addTestingProductToNewCart($productQuantity);

        $this->setOneItemLeftOnStockForTestingProduct();

        $getCartQuery = '{
            cart(cartInput: {cartUuid: "' . $newlyCreatedCart['uuid'] . '"}) {
                modifications {
                    itemModifications {
                        cartItemsWithChangedQuantity {
                            uuid
                            product {
                                uuid
                            }
                        }
                        noLongerAvailableCartItemsDueToQuantity {
                            uuid
                        }
                        noLongerListableCartItems {
                            uuid
                        }
                        cartItemsWithModifiedPrice {
                            uuid
                        }
                    }
                }
            }
        }';

        $response = $this->getResponseContentForQuery($getCartQuery);
        $modifications = $response['data']['cart']['modifications'];
        $itemModifications = $modifications['itemModifications'];

        self::assertNotEmpty($itemModifications['cartItemsWithChangedQuantity']);
        self::assertEquals($this->testingProduct->getUuid(), $itemModifications['cartItemsWithChangedQuantity'][0]['product']['uuid']);

        self::assertEmpty($itemModifications['noLongerListableCartItems']);
        self::assertEmpty($itemModifications['noLongerAvailableCartItemsDueToQuantity']);
        self::assertEmpty($itemModifications['cartItemsWithModifiedPrice']);
    }

    public function testNoLongerAvailableCartItemDueToQuantityIsReported(): void
    {
        $productQuantity = 2;
        $newlyCreatedCart = $this->addTestingProductToNewCart($productQuantity);

        $this->setNoItemLeftOnStockForTestingProduct();

        $getCartQuery = '{
            cart(cartInput: {cartUuid: "' . $newlyCreatedCart['uuid'] . '"}) {
                modifications {
                    itemModifications {
                        noLongerAvailableCartItemsDueToQuantity {
                            uuid
                            product {
                                uuid
                            }
                        }
                        noLongerListableCartItems {
                            uuid
                        }
                        cartItemsWithModifiedPrice {
                            uuid
                        }
                        cartItemsWithChangedQuantity {
                            uuid
                        }
                    }
                }
            }
        }';

        $response = $this->getResponseContentForQuery($getCartQuery);
        $modifications = $response['data']['cart']['modifications'];
        $itemModifications = $modifications['itemModifications'];

        self::assertNotEmpty($itemModifications['noLongerAvailableCartItemsDueToQuantity']);
        self::assertEquals($this->testingProduct->getUuid(), $itemModifications['noLongerAvailableCartItemsDueToQuantity'][0]['product']['uuid']);

        self::assertEmpty($itemModifications['noLongerListableCartItems']);
        self::assertEmpty($itemModifications['cartItemsWithModifiedPrice']);
        self::assertEmpty($itemModifications['cartItemsWithChangedQuantity']);
    }

    public function testTransportWithModifiedPriceIsReported(): void
    {
        $newlyCreatedCart = $this->addTestingProductToNewCart(1);
        $referenceName = TransportDataFixture::TRANSPORT_PPL;
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference($referenceName);
        $this->addTransportToCart($newlyCreatedCart['uuid'], $transport);
        $this->changeTransportPrice($referenceName);

        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . $newlyCreatedCart['uuid'] . '"
                }
            ) {
                modifications {
                    transportModifications {
                        transportPriceChanged
                    }
                }
            }
        }';

        $transportModifications = $this->getTransportModificationsForCartQuery($getCartQuery);
        self::assertTrue($transportModifications['transportPriceChanged']);
    }

    public function testTransportWithNotExistingPersonalPickupStoreIsReported(): void
    {
        $newlyCreatedCart = $this->addTestingProductToNewCart(1);
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);

        /** @var \Shopsys\FrameworkBundle\Model\Store\Store $store */
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1);
        $this->addTransportToCart($newlyCreatedCart['uuid'], $transport, $store->getUuid());
        $this->storeFacade->delete($store->getId());

        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . $newlyCreatedCart['uuid'] . '"
                }
            ) {
                modifications {
                    transportModifications {
                        personalPickupStoreUnavailable
                    }
                }
            }
        }';

        $transportModifications = $this->getTransportModificationsForCartQuery($getCartQuery);
        self::assertTrue($transportModifications['personalPickupStoreUnavailable']);
    }

    public function testValidPersonalPickupStoreIsNotReported(): void
    {
        $newlyCreatedCart = $this->addTestingProductToNewCart(1);
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);

        /** @var \Shopsys\FrameworkBundle\Model\Store\Store $store */
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1);
        $this->addTransportToCart($newlyCreatedCart['uuid'], $transport, $store->getUuid());

        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . $newlyCreatedCart['uuid'] . '"
                }
            ) {
                modifications {
                    transportModifications {
                        personalPickupStoreUnavailable
                    }
                }
            }
        }';

        $transportModifications = $this->getTransportModificationsForCartQuery($getCartQuery);
        self::assertFalse($transportModifications['personalPickupStoreUnavailable']);
    }

    public function testDeletedTransportIsReportedAsUnavailable(): void
    {
        $newlyCreatedCart = $this->addTestingProductToNewCart(1);
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL);
        $this->addTransportToCart($newlyCreatedCart['uuid'], $transport);
        $this->transportFacade->deleteById($transport->getId());
        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . $newlyCreatedCart['uuid'] . '"
                }
            ) {
                modifications {
                    transportModifications {
                        transportUnavailable
                    }
                }
            }
        }';

        $transportModifications = $this->getTransportModificationsForCartQuery($getCartQuery);
        self::assertTrue($transportModifications['transportUnavailable']);
    }

    public function testHiddenTransportIsReportedAsUnavailable(): void
    {
        $newlyCreatedCart = $this->addTestingProductToNewCart(1);
        $referenceName = TransportDataFixture::TRANSPORT_PPL;
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference($referenceName);
        $this->addTransportToCart($newlyCreatedCart['uuid'], $transport);
        $this->hideTransport($referenceName);
        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . $newlyCreatedCart['uuid'] . '"                    
                }
            ) {
                modifications {
                    transportModifications {
                        transportUnavailable
                    }
                }
            }
        }';

        $transportModifications = $this->getTransportModificationsForCartQuery($getCartQuery);
        self::assertTrue($transportModifications['transportUnavailable']);
    }

    public function testTransportWithExceededWeightLimitIsReported(): void
    {
        $newlyCreatedCart = $this->addTestingProductToNewCart(1);
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST);
        $cartUuid = $newlyCreatedCart['uuid'];

        $this->addTransportToCart($cartUuid, $transport);

        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . $cartUuid . '"
                }
            ) {
                modifications {
                    transportModifications {
                        transportWeightLimitExceeded
                    }
                }
            }
        }';

        $transportModifications = $this->getTransportModificationsForCartQuery($getCartQuery);
        self::assertFalse($transportModifications['transportWeightLimitExceeded']);

        $transportModifications = $this->addTestingProductToExistingCartAndGetTransportModifications(1, $cartUuid);
        self::assertTrue($transportModifications['transportWeightLimitExceeded']);
    }

    public function testPaymentWithModifiedPriceIsReported(): void
    {
        $newlyCreatedCart = $this->addTestingProductToNewCart(1);
        $referenceName = PaymentDataFixture::PAYMENT_CARD;
        /** @var \App\Model\Payment\Payment $payment */
        $payment = $this->getReference($referenceName);
        $cartUuid = $newlyCreatedCart['uuid'];
        $this->addPaymentToCart($cartUuid, $payment);
        $this->changePaymentPrice($referenceName);

        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . $cartUuid . '"
                }
            ) {
                modifications {
                    paymentModifications {
                        paymentPriceChanged
                    }
                }
            }
        }';

        $paymentModifications = $this->getPaymentModifications($getCartQuery);
        self::assertTrue($paymentModifications['paymentPriceChanged']);
    }

    public function testUnavailablePaymentIsReported(): void
    {
        $newlyCreatedCart = $this->addTestingProductToNewCart(1);
        /** @var \App\Model\Payment\Payment $payment */
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $cartUuid = $newlyCreatedCart['uuid'];
        $this->addPaymentToCart($cartUuid, $payment);
        $this->paymentFacade->deleteById($payment->getId());
        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . $cartUuid . '"
                }
            ) {
                modifications {
                    paymentModifications {
                        paymentUnavailable
                    }
                }
            }
        }';

        $paymentModifications = $this->getPaymentModifications($getCartQuery);
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

        return $response['data']['AddToCart']['cart'];
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
        $pricingGroupFacade = self::getContainer()->get(PricingGroupFacade::class);

        $productData = $this->productDataFactory->createFromProduct($this->testingProduct);

        foreach ($pricingGroupFacade->getAll() as $pricingGroup) {
            $productData->manualInputPricesByPricingGroupId[$pricingGroup->getId()] = Money::create(1);
        }

        $this->productFacade->edit($this->testingProduct->getId(), $productData);
        $this->handleDispatchedRecalculationMessages();
    }

    private function setOneItemLeftOnStockForTestingProduct(): void
    {
        $productData = $this->productDataFactory->createFromProduct($this->testingProduct);

        foreach ($productData->stockProductData as $stockProductData) {
            $stockProductData->productQuantity = 0;
        }

        $productData->stockProductData[1]->productQuantity = 1;

        $this->productFacade->edit($this->testingProduct->getId(), $productData);
        $this->handleDispatchedRecalculationMessages();
    }

    private function setNoItemLeftOnStockForTestingProduct(): void
    {
        $productData = $this->productDataFactory->createFromProduct($this->testingProduct);

        foreach ($productData->stockProductData as $stockProductData) {
            $stockProductData->productQuantity = 0;
        }
        $this->productFacade->editProductStockRelation($productData, $this->testingProduct);
        $this->em->clear();
        gc_collect_cycles();
    }

    /**
     * @param int $productQuantity
     * @param string $cartUuid
     * @return array
     */
    private function addTestingProductToExistingCartAndGetTransportModifications(
        int $productQuantity,
        string $cartUuid,
    ): array {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'productUuid' => $this->testingProduct->getUuid(),
            'quantity' => $productQuantity,
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'AddToCart');

        return $data['cart']['modifications']['transportModifications'];
    }

    /**
     * @param string $cartQuery
     * @return array
     */
    private function getTransportModificationsForCartQuery(string $cartQuery): array
    {
        $response = $this->getResponseContentForQuery($cartQuery);
        $data = $this->getResponseDataForGraphQlType($response, 'cart');

        return $data['modifications']['transportModifications'];
    }

    /**
     * @param string $getCartQuery
     * @return array
     */
    private function getPaymentModifications(string $getCartQuery): array
    {
        $response = $this->getResponseContentForQuery($getCartQuery);
        $modifications = $response['data']['cart']['modifications'];

        return $modifications['paymentModifications'];
    }

    /**
     * @param string $cartUuid
     * @param \App\Model\Transport\Transport $transport
     * @param string|null $pickupPlaceIdentifier
     */
    private function addTransportToCart(
        string $cartUuid,
        Transport $transport,
        ?string $pickupPlaceIdentifier = null,
    ): void {
        $pickupPlaceIdentifierLine = '';

        if ($pickupPlaceIdentifier !== null) {
            $pickupPlaceIdentifierLine = 'pickupPlaceIdentifier: "' . $pickupPlaceIdentifier . '"';
        }
        $changeTransportInCartMutation = '
            mutation {
                ChangeTransportInCart(input:{
                    cartUuid: "' . $cartUuid . '"
                    transportUuid: "' . $transport->getUuid() . '"
                    ' . $pickupPlaceIdentifierLine . '
                }) {
                    uuid
                }
            }
        ';
        $this->getResponseContentForQuery($changeTransportInCartMutation);
    }

    /**
     * @param string $transportReferenceName
     */
    private function changeTransportPrice(string $transportReferenceName): void
    {
        // refresh transport, so we're able to work with it as with an entity
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference($transportReferenceName);
        $transportData = $this->transportDataFactory->createFromTransport($transport);
        $transportData->pricesIndexedByDomainId[1] = $transport->getPrice(1)->getPrice()->add(Money::create(10));
        $this->transportFacade->edit($transport, $transportData);
    }

    /**
     * @param string $transportReferenceName
     */
    private function hideTransport(string $transportReferenceName): void
    {
        // refresh transport, so we're able to work with it as with an entity
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference($transportReferenceName);
        $transportData = $this->transportDataFactory->createFromTransport($transport);
        $transportData->hidden = true;
        $this->transportFacade->edit($transport, $transportData);
    }

    /**
     * @param string $cartUuid
     * @param \App\Model\Payment\Payment $payment
     */
    private function addPaymentToCart(string $cartUuid, Payment $payment): void
    {
        $changeTransportInCartMutation = '
            mutation {
                ChangePaymentInCart(input:{
                    cartUuid: "' . $cartUuid . '"
                    paymentUuid: "' . $payment->getUuid() . '"
                }) {
                    uuid
                }
            }
        ';
        $this->getResponseContentForQuery($changeTransportInCartMutation);
    }

    /**
     * @param string $paymentReferenceName
     */
    private function changePaymentPrice(string $paymentReferenceName): void
    {
        // refresh transport, so we're able to work with it as with an entity
        /** @var \App\Model\Payment\Payment $payment */
        $payment = $this->getReference($paymentReferenceName);
        $paymentData = $this->paymentDataFactory->createFromPayment($payment);
        $paymentData->pricesIndexedByDomainId[1] = $payment->getPrice(1)->getPrice()->add(Money::create(10));
        $this->paymentFacade->edit($payment, $paymentData);
    }
}
