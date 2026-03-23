<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class OrderWithPersonalPickupStoreTest extends GraphQlTestCase
{
    use OrderTestTrait;

    public function testCreateOrderWithPersonalPickupStore(): void
    {
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1, Store::class);

        $expected = [
            'deliveryFirstName' => 'firstName',
            'deliveryLastName' => 'lastName',
            'deliveryCompanyName' => 'Shopsys',
            'deliveryTelephone' => '+53 123456789',
            'deliveryTelephoneData' => [
                'countryCode' => 'CU',
                'prefix' => '+53',
                'number' => '123456789',
            ],
            'deliveryStreet' => $store->getStreet(),
            'deliveryCity' => $store->getCity(),
            'deliveryPostcode' => $store->getPostcode(),
            'deliveryCountry' => [
                'code' => $store->getCountry()->getCode(),
            ],
        ];

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        $cartUuid = $response['data']['AddToCart']['cart']['uuid'];

        $this->addPersonalPickupTransportToCart($cartUuid, $store->getUuid());
        $this->addCardPaymentToCart($cartUuid);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/CreateOrderWithDeliveryAddressMutation.graphql',
            [
                'cartUuid' => $cartUuid,
                'firstName' => 'firstName',
                'lastName' => 'lastName',
                'email' => 'user@example.com',
                'telephone' => new PhoneData('CU', '+53', '123456789'),
                'onCompanyBehalf' => false,
                'companyName' => 'Shopsys',
                'street' => '123 Fake Street',
                'city' => 'Springfield',
                'postcode' => '12345',
                'country' => 'CZ',
                'isDeliveryAddressDifferentFromBilling' => false,
            ],
        );

        $data = $this->getResponseDataForGraphQlType($response, 'CreateOrder');
        $this->assertSame(
            $expected,
            $data['order'],
        );
    }

    private function addPersonalPickupTransportToCart(string $cartUuid, string $pickupPlaceIdentifier): void
    {
        $transportPersonalPickup = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL, Transport::class);
        $this->addTransportToCart($cartUuid, $transportPersonalPickup, $pickupPlaceIdentifier);
    }
}
