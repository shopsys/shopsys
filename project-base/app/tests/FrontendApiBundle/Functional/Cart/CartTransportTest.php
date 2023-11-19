<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CartTransportTest extends GraphQlTestCase
{
    public function testTransportIsReturnedFromCart(): void
    {
        $this->addDemoTransportToDemoCart(TransportDataFixture::TRANSPORT_PERSONAL);

        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                }
            ) {
                transport {
                    name
                    description
                    instruction
                    position
                    daysUntilDelivery
                    transportType {
                        name
                        code
                    }
                    price {
                        priceWithVat
                        priceWithoutVat
                        vatAmount
                    },
                    images {
                        position
                        sizes {
                            url
                        }
                    },
                    stores {
                        edges {
                            node {
                                name
                            }
                        }
                    }
                }
            }
        }';

        $transportResponse = $this->getTransportResponse($getCartQuery);

        self::assertEquals($this->getExpectedTransport(), $transportResponse);
    }

    public function testTransportIsReturnedAfterAddingToCart(): void
    {
        $this->addDemoTransportToDemoCart(TransportDataFixture::TRANSPORT_PERSONAL);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);

        $addToCartMutation = 'mutation {
            AddToCart(
                input: {
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                    productUuid: "' . $product->getUuid() . '", 
                    quantity: 1
                }
            ) {
                cart {
                    transport {
                        name
                        description
                        instruction
                        position
                        daysUntilDelivery
                        transportType {
                            name
                            code
                        }
                        price {
                            priceWithVat
                            priceWithoutVat
                            vatAmount
                        },
                        images {
                            position
                            sizes {
                                url
                            }
                        },
                        stores {
                            edges {
                                node {
                                    name
                                }
                            }
                        }
                    }
                }
            }
        }';

        $transportResponse = $this->getTransportResponseAfterAddingToCart($addToCartMutation);

        self::assertEquals($this->getExpectedTransport(), $transportResponse);
    }

    /**
     * @return string[]|int[]|mixed[][]|null[]
     */
    private function getExpectedTransport(): array
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatZero */
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $this->domain->getId());

        return [
            'name' => t('Personal collection', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
            'description' => t(
                'You will be welcomed by friendly staff!',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $this->getLocaleForFirstDomain(),
            ),
            'instruction' => null,
            'position' => 2,
            'daysUntilDelivery' => 0,
            'transportType' => [
                'name' => t('Personal pickup', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'code' => 'personal_pickup',
            ],
            'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero),
            'images' => [
                [
                    'position' => null,
                    'sizes' => [
                        ['url' => $this->getFullUrlPath('/content-test/images/transport/default/58.jpg')],
                        ['url' => $this->getFullUrlPath('/content-test/images/transport/original/58.jpg')],
                    ],
                ],
            ],
            'stores' => [
                'edges' => [
                    [
                        'node' => [
                            'name' => t('Ostrava', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                        ],
                    ],
                    [
                        'node' => [
                            'name' => t('Pardubice', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testRemoveTransportFromCart(): void
    {
        $referenceName = TransportDataFixture::TRANSPORT_PERSONAL;
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference($referenceName);
        $this->addDemoTransportToDemoCart($referenceName);
        $cartQuery = 'query {
          cart(cartInput:{
            cartUuid: "' . CartDataFixture::CART_UUID . '",
          }) {
            transport {uuid}
          }
        }';
        $transportResponse = $this->getTransportResponse($cartQuery);

        self::assertEquals(['uuid' => $transport->getUuid()], $transportResponse);

        $this->removeTransportFromDemoCart();
        $transportResponse = $this->getTransportResponse($cartQuery);

        self::assertNull($transportResponse);
    }

    public function testWeightLimitTransportIsNotReturned(): void
    {
        $this->addDemoTransportToDemoCart(TransportDataFixture::TRANSPORT_CZECH_POST);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'productUuid' => $product->getUuid(),
            'quantity' => 40,
        ]);

        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                }
            ) {
                transport {
                    name
                    description
                    instruction
                }
            }
        }';

        $transportResponse = $this->getTransportResponse($getCartQuery);

        self::assertNull($transportResponse);
    }

    public function testTransportPickupPlaceIdentifierIsReturnedFromCart(): void
    {
        $this->addDemoTransportToDemoCart(TransportDataFixture::TRANSPORT_PERSONAL);
        /** @var \App\Model\Store\Store $store */
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1);
        $pickupPlaceIdentifier = $store->getUuid();
        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                }
            ) {
                selectedPickupPlaceIdentifier
            }
        }';
        $response = $this->getResponseContentForQuery($getCartQuery);
        $responseData = $this->getResponseDataForGraphQlType($response, 'cart');

        $this->assertSame($pickupPlaceIdentifier, $responseData['selectedPickupPlaceIdentifier']);
    }

    public function testTransportPickupPlaceIdentifierIsReturnedAfterAddingToCart(): void
    {
        $response = $this->addDemoTransportToDemoCart(TransportDataFixture::TRANSPORT_PERSONAL);
        /** @var \App\Model\Store\Store $store */
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1);
        $pickupPlaceIdentifier = $store->getUuid();
        $responseData = $this->getResponseDataForGraphQlType($response, 'ChangeTransportInCart');

        $this->assertSame($pickupPlaceIdentifier, $responseData['selectedPickupPlaceIdentifier']);
    }

    /**
     * @param string $transportReferenceName
     * @return mixed[]
     */
    private function addDemoTransportToDemoCart(string $transportReferenceName): array
    {
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference($transportReferenceName);
        $pickupPlaceIdentifier = null;

        if ($transportReferenceName === TransportDataFixture::TRANSPORT_PERSONAL) {
            /** @var \App\Model\Store\Store $store */
            $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1);
            $pickupPlaceIdentifier = $store->getUuid();
        }

        return $this->addTransportToDemoCart($transport->getUuid(), $pickupPlaceIdentifier);
    }

    /**
     * @param string $transportUuid
     * @param string|null $pickupPlaceIdentifier
     * @return mixed[]
     */
    private function addTransportToDemoCart(string $transportUuid, ?string $pickupPlaceIdentifier = null): array
    {
        $pickupPlaceIdentifierLine = '';

        if ($pickupPlaceIdentifier !== null) {
            $pickupPlaceIdentifierLine = 'pickupPlaceIdentifier: "' . $pickupPlaceIdentifier . '"';
        }
        $changeTransportInCartMutation = '
            mutation {
                ChangeTransportInCart(input:{
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                    transportUuid: "' . $transportUuid . '"
                    ' . $pickupPlaceIdentifierLine . '
                }) {
                    uuid
                    selectedPickupPlaceIdentifier
                }
            }
        ';

        return $this->getResponseContentForQuery($changeTransportInCartMutation);
    }

    private function removeTransportFromDemoCart(): void
    {
        $removeTransportFromCartMutation = '
            mutation {
                ChangeTransportInCart(input:{
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                    transportUuid: null
                }) {
                    uuid
                }
            }
        ';

        $this->getResponseContentForQuery($removeTransportFromCartMutation);
    }

    /**
     * @param string $getCartWithTransportQuery
     * @return array|null
     */
    private function getTransportResponse(string $getCartWithTransportQuery): ?array
    {
        $response = $this->getResponseContentForQuery($getCartWithTransportQuery);

        return $this->getResponseDataForGraphQlType($response, 'cart')['transport'];
    }

    /**
     * @param string $addToCartMutation
     * @return array|null
     */
    private function getTransportResponseAfterAddingToCart(string $addToCartMutation): ?array
    {
        $response = $this->getResponseContentForQuery($addToCartMutation);

        return $response['data']['AddToCart']['cart']['transport'];
    }
}
