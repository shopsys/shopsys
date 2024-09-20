<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Transport\TransportTypeEnum;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CartTransportTest extends GraphQlTestCase
{
    public function testTransportIsReturnedFromCart(): void
    {
        $this->addDemoTransportToDemoCart(TransportDataFixture::TRANSPORT_PERSONAL);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetCart.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
        ]);

        $transportResponse = $this->getResponseDataForGraphQlType($response, 'cart')['transport'];

        self::assertEquals($this->getExpectedTransport(), $transportResponse);
    }

    public function testTransportIsReturnedAfterAddingToCart(): void
    {
        $this->addDemoTransportToDemoCart(TransportDataFixture::TRANSPORT_PERSONAL);

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        $transportResponse = $this->getTransportResponseAfterAddingToCart($response);

        self::assertEquals($this->getExpectedTransport(), $transportResponse);
    }

    /**
     * @return array
     */
    private function getExpectedTransport(): array
    {
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $this->domain->getId(), Vat::class);

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
            'transportTypeCode' => TransportTypeEnum::TYPE_PERSONAL_PICKUP,
            'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero),
            'images' => [
                [
                    'url' => $this->getFullUrlPath('/content-test/images/transport/58.jpg'),
                    'name' => TransportDataFixture::TRANSPORT_PERSONAL,
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
                    [
                        'node' => [
                            'name' => t('Brno', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                        ],
                    ],
                    [
                        'node' => [
                            'name' => t('Praha', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                        ],
                    ],
                    [
                        'node' => [
                            'name' => t('Hradec Králové', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                        ],
                    ],
                    [
                        'node' => [
                            'name' => t('Olomouc', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                        ],
                    ],
                    [
                        'node' => [
                            'name' => t('Liberec', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                        ],
                    ],
                    [
                        'node' => [
                            'name' => t('Plzeň', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testRemoveTransportFromCart(): void
    {
        $referenceName = TransportDataFixture::TRANSPORT_PERSONAL;
        $transport = $this->getReference($referenceName, Transport::class);
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

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);

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
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1, Store::class);
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
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1, Store::class);
        $pickupPlaceIdentifier = $store->getUuid();
        $responseData = $this->getResponseDataForGraphQlType($response, 'ChangeTransportInCart');

        $this->assertSame($pickupPlaceIdentifier, $responseData['selectedPickupPlaceIdentifier']);
    }

    /**
     * @param string $transportReferenceName
     * @return array
     */
    private function addDemoTransportToDemoCart(string $transportReferenceName): array
    {
        $transport = $this->getReference($transportReferenceName, Transport::class);
        $pickupPlaceIdentifier = null;

        if ($transportReferenceName === TransportDataFixture::TRANSPORT_PERSONAL) {
            $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1, Store::class);
            $pickupPlaceIdentifier = $store->getUuid();
        }

        return $this->addTransportToDemoCart($transport->getUuid(), $pickupPlaceIdentifier);
    }

    /**
     * @param string $transportUuid
     * @param string|null $pickupPlaceIdentifier
     * @return array
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
     * @param array $response
     * @return array|null
     */
    private function getTransportResponseAfterAddingToCart(array $response): ?array
    {
        return $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart']['transport'];
    }
}
