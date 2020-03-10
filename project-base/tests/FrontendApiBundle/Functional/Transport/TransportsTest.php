<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Transport;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class TransportsTest extends GraphQlTestCase
{
    public function testPayments(): void
    {
        $query = '
            query {
                transports {
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
                    payments {
                        name
                    }
                }
            }
        ';

        $arrayExpected = [
            'data' => [
                'transports' => [
                    [
                        'name' => t('Czech post', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                        'description' => null,
                        'instruction' => null,
                        'position' => 0,
                        'price' => [
                            'priceWithVat' => '121',
                            'priceWithoutVat' => '100.00',
                            'vatAmount' => '21.00',
                        ],
                        'images' => [
                            ['url' => 'http://webserver:8080/content-test/images/transport/default/56.jpg'],
                            ['url' => 'http://webserver:8080/content-test/images/transport/original/56.jpg'],
                        ],
                        'payments' => [
                            ['name' => t('Cash on delivery', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ],
                    ],
                    [
                        'name' => t('PPL', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                        'description' => null,
                        'instruction' => null,
                        'position' => 1,
                        'price' => [
                            'priceWithVat' => '242',
                            'priceWithoutVat' => '200.00',
                            'vatAmount' => '42.00',
                        ],
                        'images' => [
                            ['url' => 'http://webserver:8080/content-test/images/transport/default/57.jpg'],
                            ['url' => 'http://webserver:8080/content-test/images/transport/original/57.jpg'],
                        ],
                        'payments' => [
                            ['name' => t('Credit card', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ],
                    ],
                    [
                        'name' => t('Personal collection', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                        'description' => t('You will be welcomed by friendly staff!', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                        'instruction' => null,
                        'position' => 2,
                        'price' => [
                            'priceWithVat' => '0',
                            'priceWithoutVat' => '0',
                            'vatAmount' => '0',
                        ],
                        'images' => [
                            ['url' => 'http://webserver:8080/content-test/images/transport/default/58.jpg'],
                            ['url' => 'http://webserver:8080/content-test/images/transport/original/58.jpg'],
                        ],
                        'payments' => [
                            ['name' => t('Credit card', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                            ['name' => t('Cash', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    /**
     * @return string
     */
    protected function getLocaleForFirstDomain(): string
    {
        return $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
    }
}
