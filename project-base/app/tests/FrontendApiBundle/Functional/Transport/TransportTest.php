<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Transport;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class TransportTest extends GraphQlTestCase
{
    protected Transport $transport;

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
}
