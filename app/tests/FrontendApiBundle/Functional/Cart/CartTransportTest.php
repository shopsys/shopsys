<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use Ramsey\Uuid\Uuid;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CartTransportTest extends GraphQlTestCase
{
    public function testTransportIsReturnedFromCart(): void
    {
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);

        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                    transport: {
                        uuid: "' . $transport->getUuid() . '"
                        price: {
                            priceWithVat: "0"
                            priceWithoutVat: "0"
                            vatAmount: "0"
                        }
                    }
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

        $response = $this->getResponseContentForQuery($getCartQuery);
        $transportResponse = $response['data']['cart']['transport'];

        self::assertEquals($this->getExpectedTransport(), $transportResponse);
    }

    public function testTransportIsReturnedAfterAddingToCart(): void
    {
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);

        $getCartQuery = 'mutation {
            AddToCart(
                input: {
                    productUuid: "' . $product->getUuid() . '", 
                    quantity: 1
                    transport: {
                        uuid: "' . $transport->getUuid() . '"
                        price: {
                            priceWithVat: "0"
                            priceWithoutVat: "0"
                            vatAmount: "0"
                        }
                    }
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

        $response = $this->getResponseContentForQuery($getCartQuery);
        $transportResponse = $response['data']['AddToCart']['transport'];

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

    public function testNotAvailableTransportIsNotReturned(): void
    {
        $nonExistentTransportUuid = Uuid::uuid4()->toString();

        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                    transport: {
                        uuid: "' . $nonExistentTransportUuid . '"
                        price: {
                            priceWithVat: "0"
                            priceWithoutVat: "0"
                            vatAmount: "0"
                        }
                    }
                }
            ) {
                transport {
                    name
                    description
                    instruction
                }
            }
        }';

        $response = $this->getResponseContentForQuery($getCartQuery);
        $transportResponse = $response['data']['cart']['transport'];

        self::assertNull($transportResponse);
    }

    public function testWeightLimitTransportIsNotReturned(): void
    {
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST);

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
                    transport: {
                        uuid: "' . $transport->getUuid() . '"
                        price: {
                            priceWithVat: "0"
                            priceWithoutVat: "0"
                            vatAmount: "0"
                        }
                    }
                }
            ) {
                transport {
                    name
                    description
                    instruction
                }
            }
        }';

        $response = $this->getResponseContentForQuery($getCartQuery);
        $transportResponse = $response['data']['cart']['transport'];

        self::assertNull($transportResponse);
    }
}
