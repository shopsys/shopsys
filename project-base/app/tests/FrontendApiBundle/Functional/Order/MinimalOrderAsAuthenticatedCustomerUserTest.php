<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class MinimalOrderAsAuthenticatedCustomerUserTest extends GraphQlWithLoginTestCase
{
    use OrderTestTrait;

    public const string DEFAULT_USER_EMAIL = CustomerUserDataFixture::USER_WITH_DELIVERY_ADDRESS_PERSISTENT_REFERENCE_EMAIL;

    public static function getDefaultInputValues(): array
    {
        return [
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'email' => 'user@example.com',
            'telephone' => new PhoneData('CU', '+53', '123456789'),
            'street' => '123 Fake Street',
            'city' => 'Springfield',
            'postcode' => '12345',
            'country' => 'CZ',
            'onCompanyBehalf' => false,
        ];
    }

    public function testMinimalOrderAsAuthenticatedUser(): void
    {
        $this->addCzechPostTransportToCart(null);
        $this->addCashOnDeliveryPaymentToCart(null);

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedOrderItems = $this->getExpectedOrderItems();

        $expected = [
            'orderCreated' => true,
            'order' => [
                'status' => t('New [adjective]', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'totalPrice' => self::getSerializedOrderTotalPriceByExpectedOrderItems(
                    $expectedOrderItems,
                ),
                'items' => $expectedOrderItems,
                'firstName' => 'firstName',
                'lastName' => 'lastName',
                'email' => self::DEFAULT_USER_EMAIL,
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
                'isDeliveryAddressDifferentFromBilling' => false,
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
            'cart' => null,
        ];

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateMinimalOrderMutation.graphql', [
            ...self::getDefaultInputValues(),
            'isDeliveryAddressDifferentFromBilling' => false,
        ]);

        $this->assertSame($expected, $this->getResponseDataForGraphQlType($response, 'CreateOrder'));
    }

    public function testMinimalOrderWithDeliveryAddressAsAuthenticatedCustomerUser(): void
    {
        $this->addCzechPostTransportToCart(null);
        $this->addCashOnDeliveryPaymentToCart(null);

        $deliveryAddress = $this->getReference(CustomerUserDataFixture::DELIVERY_ADDRESS_PERSISTENT_REFERENCE, DeliveryAddress::class);

        // Pre-fetch values before GraphQL request to avoid ORM 3 EntityIdentityCollisionException
        $expectedFirstName = $deliveryAddress->getFirstName();
        $expectedLastName = $deliveryAddress->getLastName();
        $expectedCompanyName = $deliveryAddress->getCompanyName();
        $expectedTelephone = $deliveryAddress->getTelephone();
        $expectedStreet = $deliveryAddress->getStreet();
        $expectedCity = $deliveryAddress->getCity();
        $expectedPostcode = $deliveryAddress->getPostcode();
        $expectedCountryCode = $deliveryAddress->getCountry()->getCode();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateMinimalOrderMutation.graphql', [
            ...self::getDefaultInputValues(),
            'isDeliveryAddressDifferentFromBilling' => true,
            'deliveryAddressUuid' => $deliveryAddress->getUuid(),
        ]);

        $responseData = $this->getResponseDataForGraphQlType($response, 'CreateOrder')['order'];

        $this->assertTrue($responseData['isDeliveryAddressDifferentFromBilling']);
        $this->assertSame($expectedFirstName, $responseData['deliveryFirstName']);
        $this->assertSame($expectedLastName, $responseData['deliveryLastName']);
        $this->assertSame($expectedCompanyName, $responseData['deliveryCompanyName']);
        $this->assertSame($expectedTelephone, $responseData['deliveryTelephone']);
        $this->assertSame($expectedStreet, $responseData['deliveryStreet']);
        $this->assertSame($expectedCity, $responseData['deliveryCity']);
        $this->assertSame($expectedPostcode, $responseData['deliveryPostcode']);
        $this->assertSame($expectedCountryCode, $responseData['deliveryCountry']['code']);
    }
}
