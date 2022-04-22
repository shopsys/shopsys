<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\FrontendApi\Model\Cart\CartFacade;
use App\FrontendApi\Model\Component\Constraints\TransportInOrder;
use App\Model\Cart\Transport\CartTransportFacade;
use App\Model\Store\Store;
use App\Model\Store\StoreDataFactory;
use App\Model\Store\StoreFacade;
use App\Model\Transport\TransportDataFactory;
use App\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrontendApiBundle\Component\Constraints\PaymentTransportRelation;

class TransportInOrderValidationTest extends AbstractOrderTestCase
{
    /**
     * @var \App\Model\Transport\TransportFacade
     * @inject
     */
    private TransportFacade $transportFacade;

    /**
     * @var \App\Model\Transport\TransportDataFactory
     * @inject
     */
    private TransportDataFactory $transportDataFactory;

    /**
     * @var \App\Model\Store\StoreFacade
     * @inject
     */
    private StoreFacade $storeFacade;

    /**
     * @var \App\Model\Store\StoreDataFactory
     * @inject
     */
    private StoreDataFactory $storeDataFactory;

    /**
     * @var \App\Model\Cart\Transport\CartTransportFacade
     * @inject
     */
    private CartTransportFacade $cartTransportFacade;

    /**
     * @var \App\FrontendApi\Model\Cart\CartFacade
     * @inject
     */
    private CartFacade $cartFacade;

    public function testTransportNotSet(): void
    {
        $mutation = $this->getCreateOrderMutationFromDemoCartWithCardPayment();
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInOrder::TRANSPORT_NOT_SET_ERROR, $validationErrors['input'][0]['code']);
    }

    public function testTransportUnavailable(): void
    {
        $this->addPplTransportToDemoCart();
        $this->hidePplTransport();
        $mutation = $this->getCreateOrderMutationFromDemoCartWithCardPayment();
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInOrder::TRANSPORT_UNAVAILABLE_ERROR, $validationErrors['input'][0]['code']);
    }

    public function testInvalidTransportPaymentCombination(): void
    {
        /** @var \App\Model\Transport\Transport $transportOverLimit */
        $transportOverLimit = $this->getReference(TransportDataFixture::TRANSPORT_OVER_LIMIT);
        $this->addTransportToCart(CartDataFixture::CART_UUID, $transportOverLimit);
        $mutation = $this->getCreateOrderMutationFromDemoCartWithCardPayment();
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(PaymentTransportRelation::INVALID_COMBINATION_ERROR, $validationErrors['input'][0]['code']);
    }

    public function testTransportPriceChanged(): void
    {
        $this->addPplTransportToDemoCart();
        $this->changePplTransportPrice();
        $mutation = $this->getCreateOrderMutationFromDemoCartWithCardPayment();
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInOrder::CHANGED_TRANSPORT_PRICE_ERROR, $validationErrors['input'][0]['code']);
    }

    public function testDisabledPickupPlaceUnavailable(): void
    {
        /** @var \App\Model\Store\Store $store */
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1);
        /** @var \App\Model\Transport\Transport $transportPersonal */
        $transportPersonal = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $this->addTransportToCart(CartDataFixture::CART_UUID, $transportPersonal, $store->getUuid());
        $this->disableStoreOnFirstDomain($store);
        $mutation = $this->getCreateOrderMutationFromDemoCartWithCardPayment();
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInOrder::PICKUP_PLACE_UNAVAILABLE_ERROR, $validationErrors['input'][0]['code']);
    }

    public function testDeletedPickupPlaceUnavailable(): void
    {
        /** @var \App\Model\Store\Store $store */
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1);
        /** @var \App\Model\Transport\Transport $transportPersonal */
        $transportPersonal = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $this->addTransportToCart(CartDataFixture::CART_UUID, $transportPersonal, $store->getUuid());
        $this->storeFacade->delete($store->getId());
        $mutation = $this->getCreateOrderMutationFromDemoCartWithCardPayment();
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInOrder::PICKUP_PLACE_UNAVAILABLE_ERROR, $validationErrors['input'][0]['code']);
    }

    public function testRequiredPickupPlaceIdentifier(): void
    {
        /** @var \App\Model\Store\Store $store */
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1);
        /** @var \App\Model\Transport\Transport $transportPersonal */
        $transportPersonal = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $demoCartUuid = CartDataFixture::CART_UUID;
        $this->addTransportToCart($demoCartUuid, $transportPersonal, $store->getUuid());
        $demoCart = $this->cartFacade->findCart(null, $demoCartUuid);
        $this->cartTransportFacade->unsetPickupPlaceIdentifierFromCart($demoCart);

        $mutation = $this->getCreateOrderMutationFromDemoCartWithCardPayment();
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInOrder::MISSING_PICKUP_PLACE_IDENTIFIER_ERROR, $validationErrors['input'][0]['code']);
    }

    public function testTransportWeightLimitExceeded(): void
    {
        $this->addPplTransportToDemoCart();
        $this->setPplTransportWeightLimit();
        $mutation = $this->getCreateOrderMutationFromDemoCartWithCardPayment();
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInOrder::WEIGHT_LIMIT_EXCEEDED_ERROR, $validationErrors['input'][0]['code']);
    }

    /**
     * @return string
     */
    private function getCreateOrderMutationFromDemoCartWithCardPayment(): string
    {
        $domainId = $this->domain->getId();
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatZero */
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domainId);
        /** @var \App\Model\Payment\Payment $paymentCard */
        $paymentCard = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $paymentPrice = $this->getMutationPriceConvertedToDomainDefaultCurrency('100', $vatZero);

        return 'mutation {
                    CreateOrder(
                        input: {
                            cartUuid: "' . CartDataFixture::CART_UUID . '"
                            firstName: "firstName"
                            lastName: "lastName"
                            email: "user@example.com"
                            telephone: "+53 123456789"
                            onCompanyBehalf: false
                            street: "123 Fake Street"
                            city: "Springfield"
                            postcode: "12345"
                            country: "CZ"
                            payment: {
                                uuid: "' . $paymentCard->getUuid() . '"
                                price: ' . $paymentPrice . '
                            }
                            differentDeliveryAddress: false
                        }
                    ) {
                        uuid
                    }
                }';
    }

    private function hidePplTransport(): void
    {
        /** @var \App\Model\Transport\Transport $transportPpl */
        $transportPpl = $this->getReference(TransportDataFixture::TRANSPORT_PPL);
        $transportData = $this->transportDataFactory->createFromTransport($transportPpl);
        $transportData->hidden = true;
        $this->transportFacade->edit($transportPpl, $transportData);
    }

    private function changePplTransportPrice(): void
    {
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL);
        $transportData = $this->transportDataFactory->createFromTransport($transport);
        $transportData->pricesIndexedByDomainId[1] = $transport->getPrice(1)->getPrice()->add(Money::create(10));
        $this->transportFacade->edit($transport, $transportData);
    }

    private function setPplTransportWeightLimit(): void
    {
        /** @var \App\Model\Transport\Transport $transportPpl */
        $transportPpl = $this->getReference(TransportDataFixture::TRANSPORT_PPL);
        $transportData = $this->transportDataFactory->createFromTransport($transportPpl);
        $transportData->maxWeight = 1;
        $this->transportFacade->edit($transportPpl, $transportData);
    }

    /**
     * @param \App\Model\Store\Store $store
     */
    private function disableStoreOnFirstDomain(Store $store): void
    {
        $storeData = $this->storeDataFactory->createFromStore($store);
        $storeData->isEnabledOnDomains[1] = false;
        $this->storeFacade->edit($store->getId(), $storeData);
    }
}
