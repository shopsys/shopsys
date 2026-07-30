<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Transport;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Transport\DeliveryDate\TransportExpectedDeliveryDateCalculation;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class TransportTest extends GraphQlTestCase
{
    protected Transport $transport;

    /**
     * @inject
     */
    private TransportExpectedDeliveryDateCalculation $transportExpectedDeliveryDateCalculation;

    /**
     * @inject
     */
    private CartApiFacade $cartApiFacade;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL, Transport::class);
    }

    public function testTransportNameByUuid(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/TransportQuery.graphql', [
            'uuid' => $this->transport->getUuid(),
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'transport');

        $this->assertSame(t('PPL', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()), $data['name']);
    }

    public function testGetFreeTransport(): void
    {
        $cartUuid = CartDataFixture::CART_UUID;
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'productUuid' => $product->getUuid(),
            'quantity' => 100,
        ]);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/TransportQuery.graphql', [
            'uuid' => $this->transport->getUuid(),
            'cartUuid' => $cartUuid,
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'transport');

        $this->assertSame('0.000000', $data['price']['priceWithVat']);
    }

    /**
     * Only checks that the API field is wired to the calculation, including the cartUuid propagation
     *
     * @see \Tests\FrameworkBundle\Unit\Model\Transport\DeliveryDate\TransportExpectedDeliveryDateCalculationTest for the correctness of the calculation itself
     */
    public function testExpectedDeliveryDateMatchesCalculation(): void
    {
        $standardDeliveryDate = $this->getExpectedDeliveryDateFromApi();
        $this->assertSame($this->calculateExpectedDeliveryDate(), $standardDeliveryDate);

        $cartUuid = $this->createCartWithProductAwaitingRestocking();
        $cartDeliveryDate = $this->getExpectedDeliveryDateFromApi($cartUuid);
        $this->assertSame($this->calculateExpectedDeliveryDate($cartUuid), $cartDeliveryDate);

        $this->assertNotSame(
            $standardDeliveryDate,
            $cartDeliveryDate,
            'A cart item awaiting restocking is expected to postpone the delivery date',
        );
    }

    /**
     * Only checks that the API field is wired to the calculation with the concrete store
     *
     * @see \Tests\FrameworkBundle\Unit\Model\Transport\DeliveryDate\TransportExpectedDeliveryDateCalculationTest for the store-specific calculation itself
     */
    public function testStoreExpectedDeliveryDateMatchesCalculation(): void
    {
        $personalPickupTransport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL, Transport::class);
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1, Store::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/StoreExpectedDeliveryDateQuery.graphql', [
            'storeUuid' => $store->getUuid(),
            'transportUuid' => $personalPickupTransport->getUuid(),
        ]);

        $expectedDeliveryDate = $this->transportExpectedDeliveryDateCalculation
            ->calculateExpectedDeliveryDateForStore($personalPickupTransport, null, $this->domain->getId(), $store)
            ?->format(DATE_ATOM);

        $this->assertNotNull($expectedDeliveryDate);
        $this->assertSame(
            $expectedDeliveryDate,
            $this->getResponseDataForGraphQlType($response, 'store')['expectedDeliveryDate'],
        );
    }

    public function testStoreExpectedDeliveryDateReturnsUserErrorForUnknownTransport(): void
    {
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1, Store::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/StoreExpectedDeliveryDateQuery.graphql', [
            'storeUuid' => $store->getUuid(),
            'transportUuid' => '00000000-0000-0000-0000-000000000000',
        ]);

        $this->assertUserError($response, 'transport-not-found');
    }

    public function testStoreExpectedDeliveryDateReturnsUserErrorForNonPickupTransport(): void
    {
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1, Store::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/StoreExpectedDeliveryDateQuery.graphql', [
            'storeUuid' => $store->getUuid(),
            'transportUuid' => $this->transport->getUuid(),
        ]);

        $this->assertUserError($response, 'invalid-argument');
    }

    private function getExpectedDeliveryDateFromApi(?string $cartUuid = null): ?string
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/TransportQuery.graphql', [
            'uuid' => $this->transport->getUuid(),
            'cartUuid' => $cartUuid,
        ]);

        return $this->getResponseDataForGraphQlType($response, 'transport')['expectedDeliveryDate'];
    }

    private function calculateExpectedDeliveryDate(?string $cartUuid = null): ?string
    {
        $cart = $cartUuid === null ? null : $this->cartApiFacade->findCart(null, $cartUuid);

        return $this->transportExpectedDeliveryDateCalculation
            ->calculateExpectedDeliveryDate($this->transport, $cart, $this->domain->getId())
            ?->format(DATE_ATOM);
    }

    private function createCartWithProductAwaitingRestocking(): string
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 10, Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 2,
        ]);

        return $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart']['uuid'];
    }
}
