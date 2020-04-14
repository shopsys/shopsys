<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

class FullOrderTest extends AbstractOrderTestCase
{
    public function testCreateFullOrder(): void
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
                    'companyName' => 'Airlocks s.r.o.',
                    'companyNumber' => '1234',
                    'companyTaxNumber' => 'EU4321',
                    'street' => '123 Fake Street',
                    'city' => 'Springfield',
                    'postcode' => '12345',
                    'country' => 'CZ',
                    'differentDeliveryAddress' => true,
                    'deliveryFirstName' => 'deliveryFirstName',
                    'deliveryLastName' => 'deliveryLastName',
                    'deliveryCompanyName' => null,
                    'deliveryTelephone' => null,
                    'deliveryStreet' => 'deliveryStreet',
                    'deliveryCity' => 'deliveryCity',
                    'deliveryPostcode' => '13453',
                    'deliveryCountry' => 'SK',
                    'note' => 'Thank You',
                ],
            ],
        ];

        $orderMutation = $this->getOrderMutation(__DIR__ . '/Resources/fullOrder.graphql');

        $this->assertQueryWithExpectedArray($orderMutation, $expected);
    }
}
