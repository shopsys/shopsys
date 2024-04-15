<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\Model\Customer\DeliveryAddress;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class MinimalOrderAsAuthenticatedCustomerUserTest extends GraphQlWithLoginTestCase
{
    public const DEFAULT_USER_EMAIL = CustomerUserDataFixture::USER_WITH_DELIVERY_ADDRESS_PERSISTENT_REFERENCE_EMAIL;

    use OrderTestTrait;

    public function testMinimalOrderWithDeliveryAddressAsAuthenticatedCustomerUser(): void
    {
        $this->addCzechPostTransportToCart(null);
        $this->addCashOnDeliveryPaymentToCart(null);

        $deliveryAddress = $this->getReference(CustomerUserDataFixture::DELIVERY_ADDRESS_PERSISTENT_REFERENCE, DeliveryAddress::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateMinimalOrderMutation.graphql', [
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'email' => 'user@example.com',
            'telephone' => '+53 123456789',
            'onCompanyBehalf' => false,
            'street' => '123 Fake Street',
            'city' => 'Springfield',
            'postcode' => '12345',
            'country' => 'CZ',
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
