<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\FrontendApi\Model\Component\Constraints\ExistingTransport;
use Ramsey\Uuid\Uuid;
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

        $getCartQuery = 'mutation {
            AddToCart(
                input: {
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                    productUuid: "' . $product->getUuid() . '", 
                    quantity: 1
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

        $transportResponse = $this->getTransportResponse($getCartQuery, 'AddToCart');

        self::assertEquals($this->getExpectedTransport(), $transportResponse);
    }

    /**
     * @return array
     */
    private function getExpectedTransport(): array
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatZero */
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $this->domain->getId());

        return [
            'name' => t('Personal collection', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
            'description' => t(
                'You will be welcomed by friendly staff!',
                [],
                'dataFixtures',
                $this->getLocaleForFirstDomain()
            ),
            'instruction' => null,
            'position' => 2,
            'daysUntilDelivery' => 0,
            'transportType' => [
                'name' => t('Standardní', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                'code' => 'common',
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
                            'name' => t('Ostrava', [], 'dataFixtures', $this->getFirstDomainLocale()),
                        ],
                    ],
                    [
                        'node' => [
                            'name' => t('Pardubice', [], 'dataFixtures', $this->getFirstDomainLocale()),
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testRemoveTransportFromCart(): void
    {
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $this->addTransportToDemoCart($transport->getUuid());
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

    public function testNotAvailableTransportDoesNotPassValidation(): void
    {
        $response = $this->addNonExistingTransportToDemoCart();

        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(ExistingTransport::TRANSPORT_DOES_NOT_EXIST_ERROR, $validationErrors['input.transportUuid'][0]['code']);
    }

    public function testWeightLimitTransportIsNotReturned(): void
    {
        $this->addDemoTransportToDemoCart(TransportDataFixture::TRANSPORT_CZECH_POST);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);

        $mutation = 'mutation {
            AddToCart(input: {
                cartUuid: "' . CartDataFixture::CART_UUID . '",
                productUuid: "' . $product->getUuid() . '",
                quantity: 40
            }) {
                uuid
            }
        }';
        $this->getResponseContentForQuery($mutation);

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

    /**
     * @param string $transportReferenceName
     */
    private function addDemoTransportToDemoCart(string $transportReferenceName): void
    {
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference($transportReferenceName);
        $this->addTransportToDemoCart($transport->getUuid());
    }

    /**
     * @return array
     */
    private function addNonExistingTransportToDemoCart(): array
    {
        return $this->addTransportToDemoCart(Uuid::uuid4()->toString());
    }

    /**
     * @param string $transportUuid
     * @return array
     */
    private function addTransportToDemoCart(string $transportUuid): array
    {
        $changeTransportInCartMutation = '
            mutation {
                ChangeTransportInCart(input:{
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                    transportUuid: "' . $transportUuid . '"
                }) {
                    uuid
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
     * @param string $queryOrMutationName
     * @return array|null
     */
    private function getTransportResponse(string $getCartWithTransportQuery, string $queryOrMutationName = 'cart'): ?array
    {
        $response = $this->getResponseContentForQuery($getCartWithTransportQuery);

        return $response['data'][$queryOrMutationName]['transport'];
    }
}
