<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class MinimalOrderAsAuthenticatedCustomerUserTest extends GraphQlWithLoginTestCase
{
    public const DEFAULT_USER_EMAIL = CustomerUserDataFixture::USER_WITH_DELIVERY_ADDRESS_PERSISTENT_REFERENCE_EMAIL;

    private const array DEFAULT_INPUT_VALUES = [
        'firstName' => 'firstName',
        'lastName' => 'lastName',
        'email' => 'user@example.com',
        'telephone' => '+53 123456789',
        'street' => '123 Fake Street',
        'city' => 'Springfield',
        'postcode' => '12345',
        'country' => 'CZ',
        'onCompanyBehalf' => false,
    ];

    use OrderTestTrait;

    public function testMinimalOrderAsAuthenticatedUser(): void
    {
        $this->addCzechPostTransportToCart(null);
        $this->addCashOnDeliveryPaymentToCart(null);

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

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateMinimalOrderMutation.graphql', [
            ...self::DEFAULT_INPUT_VALUES,
            'differentDeliveryAddress' => false,
        ]);

        $this->assertSame($expected, $this->getResponseDataForGraphQlType($response, 'CreateOrder'));
    }

    public function testMinimalOrderWithDeliveryAddressAsAuthenticatedCustomerUser(): void
    {
        $this->addCzechPostTransportToCart(null);
        $this->addCashOnDeliveryPaymentToCart(null);

        $deliveryAddress = $this->getReference(CustomerUserDataFixture::DELIVERY_ADDRESS_PERSISTENT_REFERENCE, DeliveryAddress::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateMinimalOrderMutation.graphql', [
            ...self::DEFAULT_INPUT_VALUES,
            'differentDeliveryAddress' => true,
            'deliveryAddressUuid' => $deliveryAddress->getUuid(),
        ]);

        $responseData = $this->getResponseDataForGraphQlType($response, 'CreateOrder')['order'];

        $this->assertTrue($responseData['differentDeliveryAddress']);
        $this->assertSame($deliveryAddress->getFirstName(), $responseData['deliveryFirstName']);
        $this->assertSame($deliveryAddress->getLastName(), $responseData['deliveryLastName']);
        $this->assertSame($deliveryAddress->getCompanyName(), $responseData['deliveryCompanyName']);
        $this->assertSame($deliveryAddress->getTelephone(), $responseData['deliveryTelephone']);
        $this->assertSame($deliveryAddress->getStreet(), $responseData['deliveryStreet']);
        $this->assertSame($deliveryAddress->getCity(), $responseData['deliveryCity']);
        $this->assertSame($deliveryAddress->getPostcode(), $responseData['deliveryPostcode']);
        $this->assertSame($deliveryAddress->getCountry()->getCode(), $responseData['deliveryCountry']['code']);
    }
}
