<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class FullOrderTest extends GraphQlTestCase
{
    use OrderTestTrait;

    public function testCreateFullOrder(): void
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
                'companyName' => 'Airlocks s.r.o.',
                'companyNumber' => '1234',
                'companyTaxNumber' => 'EU4321',
                'street' => '123 Fake Street',
                'city' => 'Springfield',
                'postcode' => '12345',
                'country' => [
                    'code' => 'CZ',
                ],
                'differentDeliveryAddress' => true,
                'deliveryFirstName' => 'deliveryFirstName',
                'deliveryLastName' => 'deliveryLastName',
                'deliveryCompanyName' => null,
                'deliveryTelephone' => null,
                'deliveryStreet' => 'deliveryStreet',
                'deliveryCity' => 'deliveryCity',
                'deliveryPostcode' => '13453',
                'deliveryCountry' => [
                    'code' => 'SK',
                ],
                'note' => 'Thank You',
                'paymentTransactionsCount' => 0,
                'isPaid' => false,
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

        $this->assertSame($this->createOrderAndGetData($cartUuid), $expected);
    }

    /**
     * @param string $cartUuid
     * @return array
     */
    private function createOrderAndGetData(string $cartUuid): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateFullOrderMutation.graphql', [
            'cartUuid' => $cartUuid,
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'email' => 'user@example.com',
            'telephone' => '+53 123456789',
            'onCompanyBehalf' => true,
            'companyName' => 'Airlocks s.r.o.',
            'companyNumber' => '1234',
            'companyTaxNumber' => 'EU4321',
            'street' => '123 Fake Street',
            'city' => 'Springfield',
            'postcode' => '12345',
            'country' => 'CZ',
            'note' => 'Thank You',
            'differentDeliveryAddress' => true,
            'deliveryFirstName' => 'deliveryFirstName',
            'deliveryLastName' => 'deliveryLastName',
            'deliveryStreet' => 'deliveryStreet',
            'deliveryCity' => 'deliveryCity',
            'deliveryCountry' => 'SK',
            'deliveryPostcode' => '13453',
        ]);

        return $this->getResponseDataForGraphQlType($response, 'CreateOrder');
    }
}
