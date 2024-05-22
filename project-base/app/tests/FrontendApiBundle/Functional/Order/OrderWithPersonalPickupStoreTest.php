<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class OrderWithPersonalPickupStoreTest extends GraphQlTestCase
{
    use OrderTestTrait;

    public function testCreateOrderWithPersonalPickupStore()
    {
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1, Store::class);

        $expected = [
            'data' => [
                'CreateOrder' => [
                    'order' => [
                        'deliveryFirstName' => 'firstName',
                        'deliveryLastName' => 'lastName',
                        'deliveryCompanyName' => 'Shopsys',
                        'deliveryTelephone' => '+53 123456789',
                        'deliveryStreet' => $store->getStreet(),
                        'deliveryCity' => $store->getCity(),
                        'deliveryPostcode' => $store->getPostcode(),
                        'deliveryCountry' => [
                            'code' => $store->getCountry()->getCode(),
                        ],
                    ],
                ],
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

        $this->assertQueryWithExpectedArray($this->getMutation($cartUuid), $expected);
    }

    /**
     * @param string $cartUuid
     * @return string
     */
    private function getMutation(string $cartUuid): string
    {
        return 'mutation {
                    CreateOrder(
                        input: {
                            cartUuid: "' . $cartUuid . '"
                            firstName: "firstName"
                            lastName: "lastName"
                            companyName: "Shopsys"
                            email: "user@example.com"
                            telephone: "+53 123456789"
                            onCompanyBehalf: false
                            street: "123 Fake Street"
                            city: "Springfield"
                            postcode: "12345"
                            country: "CZ"
                            isDeliveryAddressDifferentFromBilling: false
                        }
                    ) {
                        order {
                            deliveryFirstName
                            deliveryLastName
                            deliveryCompanyName
                            deliveryTelephone
                            deliveryStreet
                            deliveryCity
                            deliveryPostcode
                            deliveryCountry {
                                code
                            }
                        }
                    }
                }';
    }

    /**
     * @param string $cartUuid
     * @param string $pickupPlaceIdentifier
     */
    private function addPersonalPickupTransportToCart(string $cartUuid, string $pickupPlaceIdentifier): void
    {
        $transportPersonalPickup = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL, Transport::class);
        $this->addTransportToCart($cartUuid, $transportPersonalPickup, $pickupPlaceIdentifier);
    }
}
