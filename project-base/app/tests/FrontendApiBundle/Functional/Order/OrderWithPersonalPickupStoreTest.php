<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;

class OrderWithPersonalPickupStoreTest extends AbstractOrderTestCase
{
    public function testCreateOrderWithPersonalPickupStore()
    {
        /** @var \App\Model\Store\Store $store */
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1);

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

        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

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
                            differentDeliveryAddress: false
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
        /** @var \App\Model\Transport\Transport $transportPersonalPickup */
        $transportPersonalPickup = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $this->addTransportToCart($cartUuid, $transportPersonalPickup, $pickupPlaceIdentifier);
    }
}
