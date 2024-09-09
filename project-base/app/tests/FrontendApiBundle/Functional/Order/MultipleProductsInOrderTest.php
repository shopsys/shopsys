<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class MultipleProductsInOrderTest extends GraphQlTestCase
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
                'isDeliveryAddressDifferentFromBilling' => true,
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
                'heurekaAgreement' => true,
            ],
        ];
        $cartUuid = $this->addProductsToCart();
        $this->addCzechPostTransportToCart($cartUuid);
        $this->addCashOnDeliveryPaymentToCart($cartUuid);

        $this->assertSame($expected, $this->createOrderAndGetData($cartUuid));
    }

    /**
     * @return array
     */
    protected function getExpectedOrderItems(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $domainId = $this->domain->getId();
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domainId, Vat::class);

        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domainId, Vat::class);

        $helloKittyProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $hundredCrownsTicketProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72', Product::class);

        return [
            [
                'name' => $helloKittyProduct->getFullName($firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh),
                'quantity' => 1,
                'vatRate' => $vatHigh->getPercent(),
                'unit' => t('pcs', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'type' => OrderItemTypeEnum::TYPE_PRODUCT,
                'product' => [
                    'uuid' => $helloKittyProduct->getUuid(),
                ],
            ], [
                'name' => $hundredCrownsTicketProduct->getFullName($firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh, 2),
                'quantity' => 2,
                'vatRate' => $vatHigh->getPercent(),
                'unit' => t('pcs', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'type' => OrderItemTypeEnum::TYPE_PRODUCT,
                'product' => [
                    'uuid' => $hundredCrownsTicketProduct->getUuid(),
                ],
            ], [
                'name' => t('Cash on delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('49.9', $vatZero),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('49.9', $vatZero),
                'quantity' => 1,
                'vatRate' => $vatZero->getPercent(),
                'unit' => null,
                'type' => OrderItemTypeEnum::TYPE_PAYMENT,
                'product' => null,
            ], [
                'name' => t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh),
                'quantity' => 1,
                'vatRate' => $vatHigh->getPercent(),
                'unit' => null,
                'type' => OrderItemTypeEnum::TYPE_TRANSPORT,
                'product' => null,
            ],
        ];
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
            'isDeliveryAddressDifferentFromBilling' => true,
            'deliveryFirstName' => 'deliveryFirstName',
            'deliveryLastName' => 'deliveryLastName',
            'deliveryStreet' => 'deliveryStreet',
            'deliveryCity' => 'deliveryCity',
            'deliveryCountry' => 'SK',
            'deliveryPostcode' => '13453',
            'heurekaAgreement' => true,
        ]);

        return $this->getResponseDataForGraphQlType($response, 'CreateOrder');
    }

    /**
     * @return string
     */
    private function addProductsToCart(): string
    {
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product1->getUuid(),
            'quantity' => 1,
        ]);

        $cartUuid = $response['data']['AddToCart']['cart']['uuid'];

        $product72 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72', Product::class);

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'productUuid' => $product72->getUuid(),
            'quantity' => 2,
        ]);

        return $cartUuid;
    }
}
