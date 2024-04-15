<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class MinimalOrderTest extends GraphQlTestCase
{
    use OrderTestTrait;

    public function testCreateMinimalOrderMutation(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedOrderItems = $this->getExpectedOrderItems();
        $expected = [
            'order' => [
                'transport' => [
                    'name' => t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                'payment' => [
                    'name' => t('Cash on delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                'status' => t('New [adjective]', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'totalPrice' => self::getSerializedOrderTotalPriceByExpectedOrderItems(
                    $expectedOrderItems,
                ),
                'items' => $expectedOrderItems,
                'firstName' => 'firstName',
                'lastName' => 'lastName',
                'email' => 'user@example.com',
                'telephone' => '+53 123456789',
                'companyName' => null,
                'companyNumber' => null,
                'companyTaxNumber' => null,
                'street' => '123 Fake Street',
                'city' => 'Springfield',
                'postcode' => '12345',
                'country' => [
                    'code' => 'CZ',
                ],
                'differentDeliveryAddress' => false,
                'deliveryFirstName' => 'firstName',
                'deliveryLastName' => 'lastName',
                'deliveryCompanyName' => null,
                'deliveryTelephone' => '+53 123456789',
                'deliveryStreet' => '123 Fake Street',
                'deliveryCity' => 'Springfield',
                'deliveryPostcode' => '12345',
                'deliveryCountry' => [
                    'code' => 'CZ',
                ],
                'note' => null,
            ],
        ];

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        $cartUuid = $response['data']['AddToCart']['cart']['uuid'];
        $this->addCzechPostTransportToCart($cartUuid);
        $this->addCashOnDeliveryPaymentToCart($cartUuid);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateMinimalOrderMutation.graphql', [
            'cartUuid' => $cartUuid,
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'email' => 'user@example.com',
            'telephone' => '+53 123456789',
            'onCompanyBehalf' => false,
            'street' => '123 Fake Street',
            'city' => 'Springfield',
            'postcode' => '12345',
            'country' => 'CZ',
            'differentDeliveryAddress' => false,
        ]);

        $this->assertSame($expected, $this->getResponseDataForGraphQlType($response, 'CreateOrder'));
    }
}
