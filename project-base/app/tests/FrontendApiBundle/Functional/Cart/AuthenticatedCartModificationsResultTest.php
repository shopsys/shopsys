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
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;
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

    /**
     * @inject
     */
    private ProductPriceRecalculationScheduler $productPriceRecalculationScheduler;

    /**
     * @inject
     */
    private ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
    }

    public function testModificationTriggeredInAddToCartMutation(): void
    {
        $productQuantity = 2;
        $this->addTestingProductToNewCart($productQuantity);

        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 72);

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

        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 72);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $secondProduct->getUuid(),
            'quantity' => $productQuantity,
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'AddToCart');
        $cartItemUuid = $data['cart']['items'][1]['uuid'];

        // product has to be refreshed to prevent Doctrine from trying to flush not-persisted entity Vat
        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);

        $this->hideTestingProduct();

        $removeFromCartMutation = 'mutation {
            RemoveFromCart(input: {
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
        $modifications = $this->getResponseDataForGraphQlType($response, 'RemoveFromCart')['modifications'];

        self::assertNotEmpty($modifications['itemModifications']['noLongerListableCartItems']);
    }

    public function testNoLongerListableCartItemIsReported(): void
    {
        $productQuantity = 2;
        $this->addTestingProductToNewCart($productQuantity);

        $this->hideTestingProduct();

        $getCartQuery = '{
            cart {
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

        $getCartQuery = '{
            cart {
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

        $getCartQuery = '{
            cart {
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

        $getCartQuery = '{
            cart {
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
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference($referenceName);
        $this->addTransportToExistingCart($transport);
        $this->changeTransportPrice($referenceName);
        $getCartQuery = '{
            cart {
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
        $this->addTestingProductToNewCart(1);
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        /** @var \Shopsys\FrameworkBundle\Model\Store\Store $store */
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1);
        $this->addTransportToExistingCart($transport, $store->getUuid());
        $this->storeFacade->delete($store->getId());

        $getCartQuery = '{
            cart {
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
        $this->addTestingProductToNewCart(1);
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);

        /** @var \Shopsys\FrameworkBundle\Model\Store\Store $store */
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1);
        $this->addTransportToExistingCart($transport, $store->getUuid());

        $getCartQuery = '{
            cart {
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
        $this->addTestingProductToNewCart(1);
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL);
        $this->addTransportToExistingCart($transport);
        $this->transportFacade->deleteById($transport->getId());
        $getCartQuery = '{
            cart {
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
        $this->addTestingProductToNewCart(1);
        $referenceName = TransportDataFixture::TRANSPORT_PPL;
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference($referenceName);
        $this->addTransportToExistingCart($transport);
        $this->hideTransport($referenceName);
        $getCartQuery = '{
            cart {
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
        $this->addTestingProductToNewCart(1);
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST);
        $this->addTransportToExistingCart($transport);
        $getCartQuery = '{
            cart {
                modifications {
                    transportModifications {
                        transportWeightLimitExceeded
                    }
                }
            }
        }';

        $transportModifications = $this->getTransportModificationsForCartQuery($getCartQuery);
        self::assertFalse($transportModifications['transportWeightLimitExceeded']);

        $transportModifications = $this->addTestingProductToExistingCartAndGetTransportModifications(1);
        self::assertTrue($transportModifications['transportWeightLimitExceeded']);
    }

    public function testPaymentWithModifiedPriceIsReported(): void
    {
        $this->addTestingProductToNewCart(1);
        $referenceName = PaymentDataFixture::PAYMENT_CARD;
        /** @var \App\Model\Payment\Payment $payment */
        $payment = $this->getReference($referenceName);
        $this->addPaymentToExistingCart($payment);
        $this->changePaymentPrice($referenceName);

        $getCartQuery = '{
            cart {
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
        $this->addTestingProductToNewCart(1);
        /** @var \App\Model\Payment\Payment $payment */
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $this->addPaymentToExistingCart($payment);
        $this->paymentFacade->deleteById($payment->getId());
        $getCartQuery = '{
            cart {
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

        // product has to be refreshed to prevent Doctrine from trying to flush not-persisted entity Vat
        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);

        return $this->getResponseDataForGraphQlType($response, 'AddToCart');
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
        $pricingGroupFacade = self::getContainer()->get(PricingGroupFacade::class);

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

        $this->productFacade->editProductStockRelation($productData, $this->testingProduct);
        $this->productPriceRecalculationScheduler->reset();
        $this->productAvailabilityRecalculationScheduler->cleanScheduleForImmediateRecalculation();
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
     * @param string $getCartQuery
     * @return array
     */
    private function getTransportModificationsForCartQuery(string $getCartQuery): array
    {
        $response = $this->getResponseContentForQuery($getCartQuery);
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
        $data = $this->getResponseDataForGraphQlType($response, 'cart');

        return $data['modifications']['paymentModifications'];
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param string|null $pickupPlaceIdentifier
     */
    private function addTransportToExistingCart(Transport $transport, ?string $pickupPlaceIdentifier = null): void
    {
        $pickupPlaceIdentifierLine = '';

        if ($pickupPlaceIdentifier !== null) {
            $pickupPlaceIdentifierLine = 'pickupPlaceIdentifier: "' . $pickupPlaceIdentifier . '"';
        }
        $changeTransportInCartMutation = '
            mutation {
                ChangeTransportInCart(input:{
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
     * @param \App\Model\Payment\Payment $payment
     */
    private function addPaymentToExistingCart(Payment $payment): void
    {
        $changeTransportInCartMutation = '
            mutation {
                ChangePaymentInCart(input:{
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
        /** @var \App\Model\Payment\Payment $payment */
        $payment = $this->getReference($paymentReferenceName);
        $paymentData = $this->paymentDataFactory->createFromPayment($payment);
        $paymentData->pricesIndexedByDomainId[1] = $payment->getPrice(1)->getPrice()->add(Money::create(10));
        $this->paymentFacade->edit($payment, $paymentData);
    }
}
