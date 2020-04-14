<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

class MinimalOrderTest extends AbstractOrderTestCase
{
    public function testCreateMinimalOrderMutation(): void
    {
        $expected = [
            'data' => [
                'CreateOrder' => [
                    'transport' => [
                        'name' => 'Czech post',
                    ],
                    'payment' => [
                        'name' => 'Cash on delivery',
                    ],
                    'status' => 'New',
                    'totalPrice' => [
                        'priceWithVat' => '1406.44',
                        'priceWithoutVat' => '1162.69',
                        'vatAmount' => '243.75',
                    ],
                    'items' => $this->getExpectedOrderItems(),
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
                    'country' => 'CZ',
                    'differentDeliveryAddress' => false,
                    'deliveryFirstName' => 'firstName',
                    'deliveryLastName' => 'lastName',
                    'deliveryCompanyName' => null,
                    'deliveryTelephone' => '+53 123456789',
                    'deliveryStreet' => '123 Fake Street',
                    'deliveryCity' => 'Springfield',
                    'deliveryPostcode' => '12345',
                    'deliveryCountry' => 'CZ',
                    'note' => null,
                ],
            ],
        ];

        $orderMutation = $this->getOrderMutation(__DIR__ . '/Resources/minimalOrder.graphql');

        $this->assertQueryWithExpectedArray($orderMutation, $expected);
    }
}
