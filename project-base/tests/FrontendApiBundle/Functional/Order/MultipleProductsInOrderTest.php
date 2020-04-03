<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;

class MultipleProductsInOrderTest extends AbstractOrderTestCase
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
                        'priceWithVat' => '4964.44',
                        'priceWithoutVat' => '4103.19',
                        'vatAmount' => '861.25',
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

        $orderMutation = $this->getOrderMutation(__DIR__ . '/Resources/multipleProductsInOrder.graphql');

        $this->assertQueryWithExpectedArray($orderMutation, $expected);
    }

    /**
     * @param string $filePath
     * @return string
     */
    protected function getOrderMutation(string $filePath): string
    {
        $mutation = parent::getOrderMutation($filePath);

        $replaces = [
            '___UUID_PRODUCT_2___' => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72')->getUuid(),
            '___UUID_PRODUCT_3___' => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '80')->getUuid(),
            '___UUID_PRODUCT_4___' => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '81')->getUuid(),
            '___UUID_PRODUCT_5___' => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '77')->getUuid(),
            '___UUID_PRODUCT_6___' => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '2')->getUuid(),
        ];

        return strtr($mutation, $replaces);
    }

    /**
     * @return array
     */
    protected function getExpectedOrderItems(): array
    {
        return [
            0 => [
                'name' => '22" Sencor SLE 22F46DM4 HELLO KITTY',
                'unitPrice' => [
                    'priceWithVat' => '139.96',
                    'priceWithoutVat' => '115.67',
                    'vatAmount' => '24.29',
                ],
                'totalPrice' => [
                    'priceWithVat' => '1399.60',
                    'priceWithoutVat' => '1156.69',
                    'vatAmount' => '242.91',
                ],
                'quantity' => 10,
                'vatRate' => '21.0000',
                'unit' => 'pcs',
            ],
            1 => [
                'name' => '100 Czech crowns ticket',
                'unitPrice' => [
                    'priceWithVat' => '4.84',
                    'priceWithoutVat' => '4.00',
                    'vatAmount' => '0.84',
                ],
                'totalPrice' => [
                    'priceWithVat' => '484.00',
                    'priceWithoutVat' => '400.00',
                    'vatAmount' => '84.00',
                ],
                'quantity' => 100,
                'vatRate' => '21.0000',
                'unit' => 'pcs',
            ],
            2 => [
                'name' => '27” Hyundai T27D590EY',
                'unitPrice' => [
                    'priceWithVat' => '300.03',
                    'priceWithoutVat' => '247.96',
                    'vatAmount' => '52.07',
                ],
                'totalPrice' => [
                    'priceWithVat' => '300.03',
                    'priceWithoutVat' => '247.96',
                    'vatAmount' => '52.07',
                ],
                'quantity' => 1,
                'vatRate' => '21.0000',
                'unit' => 'pcs',
            ],
            3 => [
                'name' => '27” Hyundai T27D590EZ',
                'unitPrice' => [
                    'priceWithVat' => '309.71',
                    'priceWithoutVat' => '255.96',
                    'vatAmount' => '53.75',
                ],
                'totalPrice' => [
                    'priceWithVat' => '619.42',
                    'priceWithoutVat' => '511.92',
                    'vatAmount' => '107.50',
                ],
                'quantity' => 2,
                'vatRate' => '21.0000',
                'unit' => 'pcs',
            ],
            4 => [
                'name' => '30” Hyundai 22MT44D',
                'unitPrice' => [
                    'priceWithVat' => '193.55',
                    'priceWithoutVat' => '159.96',
                    'vatAmount' => '33.59',
                ],
                'totalPrice' => [
                    'priceWithVat' => '967.75',
                    'priceWithoutVat' => '799.79',
                    'vatAmount' => '167.96',
                ],
                'quantity' => 5,
                'vatRate' => '21.0000',
                'unit' => 'pcs',
            ],
            5 => [
                'name' => '32" Philips 32PFL4308',
                'unitPrice' => [
                    'priceWithVat' => '395.60',
                    'priceWithoutVat' => '326.94',
                    'vatAmount' => '68.66',
                ],
                'totalPrice' => [
                    'priceWithVat' => '1186.80',
                    'priceWithoutVat' => '980.83',
                    'vatAmount' => '205.97',
                ],
                'quantity' => 3,
                'vatRate' => '21.0000',
                'unit' => 'pcs',
            ],
            6 => [
                'name' => 'Cash on delivery',
                'unitPrice' => [
                    'priceWithVat' => '2.00',
                    'priceWithoutVat' => '2.00',
                    'vatAmount' => '0.00',
                ],
                'totalPrice' => [
                    'priceWithVat' => '2.00',
                    'priceWithoutVat' => '2.00',
                    'vatAmount' => '0.00',
                ],
                'quantity' => 1,
                'vatRate' => '0.0000',
                'unit' => null,
            ],
            7 => [
                'name' => 'Czech post',
                'unitPrice' => [
                    'priceWithVat' => '4.84',
                    'priceWithoutVat' => '4.00',
                    'vatAmount' => '0.84',
                ],
                'totalPrice' => [
                    'priceWithVat' => '4.84',
                    'priceWithoutVat' => '4.00',
                    'vatAmount' => '0.84',
                ],
                'quantity' => 1,
                'vatRate' => '21.0000',
                'unit' => null,
            ],
        ];
    }
}
