<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportDataFactory;
use App\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Transport\CartTransportFacade;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Shopsys\FrontendApiBundle\Component\Constraints\PaymentTransportRelation;
use Shopsys\FrontendApiBundle\Component\Constraints\TransportInOrder;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class TransportInOrderValidationTest extends GraphQlTestCase
{
    use OrderTestTrait;

    /**
     * @inject
     */
    private TransportFacade $transportFacade;

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
    private CartTransportFacade $cartTransportFacade;

    /**
     * @inject
     */
    private CartApiFacade $cartApiFacade;

    public function testTransportNotSet(): void
    {
        $this->addCardPaymentToDemoCart();
        $mutation = $this->getCreateOrderMutationFromDemoCart();
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInOrder::TRANSPORT_NOT_SET_ERROR, $validationErrors['input'][0]['code']);
    }

    public function testTransportUnavailable(): void
    {
        $this->addCardPaymentToDemoCart();
        $this->addPplTransportToCart(CartDataFixture::CART_UUID);
        $this->hidePplTransport();
        $mutation = $this->getCreateOrderMutationFromDemoCart();
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInOrder::TRANSPORT_UNAVAILABLE_ERROR, $validationErrors['input'][0]['code']);
    }

    public function testInvalidTransportPaymentCombination(): void
    {
        $this->addCardPaymentToDemoCart();
        $cart = $this->cartApiFacade->findCart(null, CartDataFixture::CART_UUID);
        $transportDrone = $this->getReference(TransportDataFixture::TRANSPORT_DRONE, Transport::class);
        $this->cartTransportFacade->updateTransportInCart($cart, $transportDrone->getUuid(), null);
        $mutation = $this->getCreateOrderMutationFromDemoCart();
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(PaymentTransportRelation::INVALID_COMBINATION_ERROR, $validationErrors['input'][0]['code']);
    }

    public function testTransportPriceChanged(): void
    {
        $this->addCardPaymentToDemoCart();
        $this->addPplTransportToCart(CartDataFixture::CART_UUID);
        $this->changePplTransportPrice();
        $mutation = $this->getCreateOrderMutationFromDemoCart();
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('CreateOrder', $response['data']);
        $this->assertArrayHasKey('cart', $response['data']['CreateOrder']);
        $this->assertArrayHasKey('modifications', $response['data']['CreateOrder']['cart']);
        $this->assertArrayHasKey('transportModifications', $response['data']['CreateOrder']['cart']['modifications']);
        $this->assertArrayHasKey('transportPriceChanged', $response['data']['CreateOrder']['cart']['modifications']['transportModifications']);
        $this->assertTrue($response['data']['CreateOrder']['cart']['modifications']['transportModifications']['transportPriceChanged']);
    }

    public function testDeletedPickupPlaceUnavailable(): void
    {
        $this->addCardPaymentToDemoCart();
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1, Store::class);
        $transportPersonal = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL, Transport::class);
        $this->addTransportToCart(CartDataFixture::CART_UUID, $transportPersonal, $store->getUuid());
        $this->storeFacade->delete($store->getId());
        $mutation = $this->getCreateOrderMutationFromDemoCart();
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('CreateOrder', $response['data']);
        $this->assertArrayHasKey('cart', $response['data']['CreateOrder']);
        $this->assertArrayHasKey('modifications', $response['data']['CreateOrder']['cart']);
        $this->assertArrayHasKey('transportModifications', $response['data']['CreateOrder']['cart']['modifications']);
        $this->assertArrayHasKey('personalPickupStoreUnavailable', $response['data']['CreateOrder']['cart']['modifications']['transportModifications']);
        $this->assertTrue($response['data']['CreateOrder']['cart']['modifications']['transportModifications']['personalPickupStoreUnavailable']);
    }

    public function testRequiredPickupPlaceIdentifier(): void
    {
        $this->addCardPaymentToDemoCart();
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1, Store::class);
        $transportPersonal = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL, Transport::class);
        $demoCartUuid = CartDataFixture::CART_UUID;
        $this->addTransportToCart($demoCartUuid, $transportPersonal, $store->getUuid());
        $demoCart = $this->cartApiFacade->findCart(null, $demoCartUuid);
        $this->cartTransportFacade->unsetPickupPlaceIdentifierFromCart($demoCart);

        $mutation = $this->getCreateOrderMutationFromDemoCart();
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(TransportInOrder::MISSING_PICKUP_PLACE_IDENTIFIER_ERROR, $validationErrors['input'][0]['code']);
    }

    public function testTransportWeightLimitExceeded(): void
    {
        $this->addCardPaymentToDemoCart();
        $this->addPplTransportToCart(CartDataFixture::CART_UUID);
        $this->setPplTransportWeightLimit();
        $mutation = $this->getCreateOrderMutationFromDemoCart();
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('CreateOrder', $response['data']);
        $this->assertArrayHasKey('cart', $response['data']['CreateOrder']);
        $this->assertArrayHasKey('modifications', $response['data']['CreateOrder']['cart']);
        $this->assertArrayHasKey('transportModifications', $response['data']['CreateOrder']['cart']['modifications']);
        $this->assertArrayHasKey('transportWeightLimitExceeded', $response['data']['CreateOrder']['cart']['modifications']['transportModifications']);
        $this->assertTrue($response['data']['CreateOrder']['cart']['modifications']['transportModifications']['transportWeightLimitExceeded']);
    }

    private function hidePplTransport(): void
    {
        $transportPpl = $this->getReference(TransportDataFixture::TRANSPORT_PPL, Transport::class);
        $transportData = $this->transportDataFactory->createFromTransport($transportPpl);
        $transportData->hidden = true;
        $this->transportFacade->edit($transportPpl, $transportData);
    }

    private function changePplTransportPrice(): void
    {
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL, Transport::class);
        $transportData = $this->transportDataFactory->createFromTransport($transport);
        $transportData->pricesIndexedByDomainId[1] = $transport->getPrice(1)->getPrice()->add(Money::create(10));
        $this->transportFacade->edit($transport, $transportData);
    }

    private function setPplTransportWeightLimit(): void
    {
        $transportPpl = $this->getReference(TransportDataFixture::TRANSPORT_PPL, Transport::class);
        $transportData = $this->transportDataFactory->createFromTransport($transportPpl);
        $transportData->maxWeight = 1;
        $this->transportFacade->edit($transportPpl, $transportData);
    }
}
