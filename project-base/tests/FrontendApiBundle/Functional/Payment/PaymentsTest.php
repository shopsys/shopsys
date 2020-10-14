<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use App\DataFixtures\Demo\VatDataFixture;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaymentsTest extends GraphQlTestCase
{
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

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatZero */
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $this->domain->getId());

        $arrayExpected = [
            'data' => [
                'payments' => [
                    [
                        'name' => t('Credit card', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                        'description' => t(
                            'Quick, cheap and reliable!',
                            [],
                            'dataFixtures',
                            $this->getLocaleForFirstDomain()
                        ),
                        'instruction' => null,
                        'position' => 0,
                        'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatZero),
                        'images' => [
                            ['url' => $this->getFullUrlPath('/content-test/images/payment/default/53.jpg')],
                            ['url' => $this->getFullUrlPath('/content-test/images/payment/original/53.jpg')],
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
                        'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('50', $vatZero),
                        'images' => [
                            ['url' => $this->getFullUrlPath('/content-test/images/payment/default/55.jpg')],
                            ['url' => $this->getFullUrlPath('/content-test/images/payment/original/55.jpg')],
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
                        'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero),
                        'images' => [
                            ['url' => $this->getFullUrlPath('/content-test/images/payment/default/54.jpg')],
                            ['url' => $this->getFullUrlPath('/content-test/images/payment/original/54.jpg')],
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
