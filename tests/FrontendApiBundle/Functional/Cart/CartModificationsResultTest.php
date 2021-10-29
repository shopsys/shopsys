<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Payment\PaymentFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use App\Model\Transport\TransportFacade;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CartModificationsResultTest extends GraphQlTestCase
{
    /**
     * @var \App\Model\Product\Product
     */
    private Product $testingProduct;

    /**
     * @var \App\Model\Product\ProductFacade
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @var \App\Model\Product\ProductDataFactory
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    /**
     * @var \App\Model\Transport\TransportFacade
     * @inject
     */
    private TransportFacade $transportFacade;

    /**
     * @var \App\Model\Payment\PaymentFacade
     * @inject
     */
    private PaymentFacade $paymentFacade;

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

        $addToCartMutation = 'mutation {
            AddToCart(input: {
                cartUuid: "' . $newlyCreatedCart['uuid'] . '"
                productUuid: "' . $secondProduct->getUuid() . '",
                quantity: ' . $productQuantity . '
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
        $response = $this->getResponseContentForQuery($addToCartMutation);
        $modifications = $response['data']['AddToCart']['modifications'];

        self::assertNotEmpty($modifications['itemModifications']['noLongerListableCartItems']);
    }

    public function testModificationTriggeredInRemoveFromCartMutation(): void
    {
        $productQuantity = 2;
        $newlyCreatedCart = $this->addTestingProductToNewCart($productQuantity);

        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 72);
        $addToCartMutation = 'mutation {
            AddToCart(input: {
                cartUuid: "' . $newlyCreatedCart['uuid'] . '"
                productUuid: "' . $secondProduct->getUuid() . '",
                quantity: ' . $productQuantity . '
            }) {
                items {
                    uuid
                }
            }
        }';
        $response = $this->getResponseContentForQuery($addToCartMutation);
        $cartItemUuid = $response['data']['AddToCart']['items'][1]['uuid'];

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
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $inputTransportPrice = $transport->getPrice(1)->getPrice()->add(Money::create(10))->getAmount();
        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . $newlyCreatedCart['uuid'] . '"
                    transport: {
                        uuid: "' . $transport->getUuid() . '"
                        price: {
                            priceWithVat: "' . $inputTransportPrice . '"
                            priceWithoutVat: "' . $inputTransportPrice . '"
                            vatAmount: "0"
                        }
                    }
                }
            ) {
                modifications {
                    transportModifications {
                        transportPriceChanged
                    }
                }
            }
        }';

        $transportModifications = $this->getTransportModifications($getCartQuery);
        self::assertTrue($transportModifications['transportPriceChanged']);
    }

    public function testTransportWithNotExistingPersonalPickupStoreIsReported(): void
    {
        $newlyCreatedCart = $this->addTestingProductToNewCart(1);
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);

        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . $newlyCreatedCart['uuid'] . '"
                    transport: {
                        uuid: "' . $transport->getUuid() . '"
                        price: {
                            priceWithVat: "0"
                            priceWithoutVat: "0"
                            vatAmount: "0"
                        }
                        pickupPlaceIdentifier: "' . Uuid::uuid4()->toString() . '"
                    }
                }
            ) {
                modifications {
                    transportModifications {
                        personalPickupStoreUnavailable
                    }
                }
            }
        }';

        $transportModifications = $this->getTransportModifications($getCartQuery);
        self::assertTrue($transportModifications['personalPickupStoreUnavailable']);
    }

    public function testValidPersonalPickupStoreIsNotReported(): void
    {
        $newlyCreatedCart = $this->addTestingProductToNewCart(1);
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);

        /** @var \App\Model\Store\Store $store */
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1);

        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . $newlyCreatedCart['uuid'] . '"
                    transport: {
                        uuid: "' . $transport->getUuid() . '"
                        price: {
                            priceWithVat: "0"
                            priceWithoutVat: "0"
                            vatAmount: "0"
                        }
                        pickupPlaceIdentifier: "' . $store->getUuid() . '"
                    }
                }
            ) {
                modifications {
                    transportModifications {
                        personalPickupStoreUnavailable
                    }
                }
            }
        }';

        $transportModifications = $this->getTransportModifications($getCartQuery);
        self::assertFalse($transportModifications['personalPickupStoreUnavailable']);
    }

    public function testUnavailableTransportIsReported(): void
    {
        $newlyCreatedCart = $this->addTestingProductToNewCart(1);
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $inputTransportPrice = $transport->getPrice(1)->getPrice()->getAmount();
        $transportUuid = $transport->getUuid();
        $this->transportFacade->deleteById($transport->getId());
        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . $newlyCreatedCart['uuid'] . '"
                    transport: {
                        uuid: "' . $transportUuid . '"
                        price: {
                            priceWithVat: "' . $inputTransportPrice . '"
                            priceWithoutVat: "' . $inputTransportPrice . '"
                            vatAmount: "0"
                        }
                    }
                }
            ) {
                modifications {
                    transportModifications {
                        transportUnavailable
                    }
                }
            }
        }';

        $transportModifications = $this->getTransportModifications($getCartQuery);
        self::assertTrue($transportModifications['transportUnavailable']);
    }

    public function testTransportWithExceededWeightLimitIsReported(): void
    {
        $newlyCreatedCart = $this->addTestingProductToNewCart(1);
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST);
        $inputTransportPrice = $transport->getPrice(1)->getPrice()->getAmount();
        $cartUuid = $newlyCreatedCart['uuid'];
        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . $cartUuid . '"
                    transport: {
                        uuid: "' . $transport->getUuid() . '"
                        price: {
                            priceWithVat: "' . $inputTransportPrice . '"
                            priceWithoutVat: "' . $inputTransportPrice . '"
                            vatAmount: "0"
                        }
                    }
                }
            ) {
                modifications {
                    transportModifications {
                        transportWeightLimitExceeded
                    }
                }
            }
        }';

        $transportModifications = $this->getTransportModifications($getCartQuery);
        self::assertFalse($transportModifications['transportWeightLimitExceeded']);

        $this->addTestingProductToExistingCart(1, $cartUuid);
        $transportModifications = $this->getTransportModifications($getCartQuery);
        self::assertTrue($transportModifications['transportWeightLimitExceeded']);
    }

    public function testPaymentWithModifiedPriceIsReported(): void
    {
        $newlyCreatedCart = $this->addTestingProductToNewCart(1);
        /** @var \App\Model\Transport\Transport $payment */
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $inputPaymentPrice = $payment->getPrice(1)->getPrice()->add(Money::create(10))->getAmount();
        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . $newlyCreatedCart['uuid'] . '"
                    payment: {
                        uuid: "' . $payment->getUuid() . '"
                        price: {
                            priceWithVat: "' . $inputPaymentPrice . '"
                            priceWithoutVat: "' . $inputPaymentPrice . '"
                            vatAmount: "0"
                        }
                    }
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
        /** @var \App\Model\Transport\Transport $payment */
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $inputPaymentPrice = $payment->getPrice(1)->getPrice()->getAmount();
        $paymentUuid = $payment->getUuid();
        $this->paymentFacade->deleteById($payment->getId());
        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . $newlyCreatedCart['uuid'] . '"
                    payment: {
                        uuid: "' . $paymentUuid . '"
                        price: {
                            priceWithVat: "' . $inputPaymentPrice . '"
                            priceWithoutVat: "' . $inputPaymentPrice . '"
                            vatAmount: "0"
                        }
                    }
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
        $mutation = 'mutation {
            AddToCart(input: {
                productUuid: "' . $this->testingProduct->getUuid() . '",
                quantity: ' . $productQuantity . '
            }) {
                uuid
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        return $response['data']['AddToCart'];
    }

    private function hideTestingProduct(): void
    {
        $productData = $this->productDataFactory->createFromProduct($this->testingProduct);
        $productData->sellingDenied = true;

        $this->productFacade->edit($this->testingProduct->getId(), $productData);
        $this->dispatchFakeKernelResponseEventToTriggerImmediateRecalculations();
    }

    private function modifyPriceOfTestingProduct(): void
    {
        $pricingGroupFacade = $this->getContainer()->get(PricingGroupFacade::class);

        $productData = $this->productDataFactory->createFromProduct($this->testingProduct);

        foreach ($pricingGroupFacade->getAll() as $pricingGroup) {
            $productData->manualInputPricesByPricingGroupId[$pricingGroup->getId()] = Money::create(1);
        }

        $this->productFacade->edit($this->testingProduct->getId(), $productData);
        $this->dispatchFakeKernelResponseEventToTriggerImmediateRecalculations();
    }

    private function setOneItemLeftOnStockForTestingProduct(): void
    {
        $productData = $this->productDataFactory->createFromProduct($this->testingProduct);

        foreach ($productData->stockProductData as $stockProductData) {
            $stockProductData->productQuantity = 0;
        }

        $productData->stockProductData[1]->productQuantity = 1;

        $this->productFacade->edit($this->testingProduct->getId(), $productData);
        $this->dispatchFakeKernelResponseEventToTriggerImmediateRecalculations();
    }

    private function setNoItemLeftOnStockForTestingProduct(): void
    {
        $productData = $this->productDataFactory->createFromProduct($this->testingProduct);

        foreach ($productData->stockProductData as $stockProductData) {
            $stockProductData->productQuantity = 0;
        }

        $this->productFacade->edit($this->testingProduct->getId(), $productData);
        $this->dispatchFakeKernelResponseEventToTriggerImmediateRecalculations();
    }

    /**
     * @param int $productQuantity
     * @param string $cartUuid
     */
    private function addTestingProductToExistingCart(int $productQuantity, string $cartUuid): void
    {
        $mutation = 'mutation {
            AddToCart(input: {
                cartUuid:"' . $cartUuid . '"
                productUuid: "' . $this->testingProduct->getUuid() . '"
                quantity: ' . $productQuantity . '
            }) {
                uuid
            }
        }';

        $this->getResponseContentForQuery($mutation);
    }

    /**
     * @param string $getCartQuery
     * @return array
     */
    private function getTransportModifications(string $getCartQuery): array
    {
        $response = $this->getResponseContentForQuery($getCartQuery);
        $modifications = $response['data']['cart']['modifications'];

        return $modifications['transportModifications'];
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
}
