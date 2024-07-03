<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Transport;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class TransportTest extends GraphQlTestCase
{
    protected Transport $transport;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL, Transport::class);
    }

    public function testTransportNameByUuid(): void
    {
        $query = '
            query {
                transport(uuid: "' . $this->transport->getUuid() . '") {
                    name
                }
            }
        ';

        $arrayExpected = [
            'data' => [
                'transport' => [
                    'name' => t('PPL', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
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

        $query = '
            query {
                transport(uuid: "' . $this->transport->getUuid() . '") {
                    price(cartUuid: "' . $cartUuid . '") {
                        priceWithVat
                    }
                }
            }
        ';

        $arrayExpected = [
            'data' => [
                'transport' => [
                    'price' => [
                        'priceWithVat' => '0.000000',
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }
}
