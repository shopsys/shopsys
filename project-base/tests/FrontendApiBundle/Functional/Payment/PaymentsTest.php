<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaymentsTest extends GraphQlTestCase
{
    /**
     * @var mixed
     */
    private $webserverUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webserverUrl = $this->getContainer()->getParameter('overwrite_domain_url');
    }

    public function testPayments(): void
    {
        $query = '
            query {
                payments {
                    name,
                    description,
                    instruction,
                    position,
                    price {
                        priceWithVat
                        priceWithoutVat
                        vatAmount
                    },
                    images {
                        url
                    },
                    transports {
                        name
                    }
                }
            }
        ';

        $arrayExpected = [
            'data' => [
                'payments' => [
                    [
                        'name' => t('Credit card', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                        'description' => t('Quick, cheap and reliable!', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                        'instruction' => null,
                        'position' => 0,
                        'price' => [
                            'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('100'),
                            'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('100.00'),
                            'vatAmount' => '0.00',
                        ],
                        'images' => [
                            ['url' => $this->webserverUrl . '/content-test/images/payment/default/53.jpg'],
                            ['url' => $this->webserverUrl . '/content-test/images/payment/original/53.jpg'],
                        ],
                        'transports' => [
                            ['name' => t('PPL', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                            ['name' => t('Personal collection', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ],
                    ],
                    [
                        'name' => t('Cash on delivery', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                        'description' => null,
                        'instruction' => null,
                        'position' => 1,
                        'price' => [
                            'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('50'),
                            'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('50.00'),
                            'vatAmount' => '0.00',
                        ],
                        'images' => [
                            ['url' => $this->webserverUrl . '/content-test/images/payment/default/55.jpg'],
                            ['url' => $this->webserverUrl . '/content-test/images/payment/original/55.jpg'],
                        ],
                        'transports' => [
                            ['name' => t('Czech post', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ],
                    ],
                    [
                        'name' => t('Cash', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                        'description' => null,
                        'instruction' => null,
                        'position' => 2,
                        'price' => [
                            'priceWithVat' => '0.00',
                            'priceWithoutVat' => '0.00',
                            'vatAmount' => '0.00',
                        ],
                        'images' => [
                            ['url' => $this->webserverUrl . '/content-test/images/payment/default/54.jpg'],
                            ['url' => $this->webserverUrl . '/content-test/images/payment/original/54.jpg'],
                        ],
                        'transports' => [
                            ['name' => t('Personal collection', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }
}
