<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Transport;

use App\DataFixtures\Demo\VatDataFixture;
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

        $domainId = $this->domain->getId();
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatHigh */
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domainId);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatZero */
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domainId);

        $arrayExpected = [
            'data' => [
                'transports' => [
                    [
                        'name' => t('Czech post', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                        'description' => null,
                        'instruction' => null,
                        'position' => 0,
                        'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh),
                        'images' => [
                            ['url' => $this->getFullUrlPath('/content-test/images/transport/default/56.jpg')],
                            ['url' => $this->getFullUrlPath('/content-test/images/transport/original/56.jpg')],
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
                        'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('200', $vatHigh),
                        'images' => [
                            ['url' => $this->getFullUrlPath('/content-test/images/transport/default/57.jpg')],
                            ['url' => $this->getFullUrlPath('/content-test/images/transport/original/57.jpg')],
                        ],
                        'payments' => [
                            ['name' => t('Credit card', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ],
                    ],
                    [
                        'name' => t('Personal collection', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                        'description' => t(
                            'You will be welcomed by friendly staff!',
                            [],
                            'dataFixtures',
                            $this->getLocaleForFirstDomain()
                        ),
                        'instruction' => null,
                        'position' => 2,
                        'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero),
                        'images' => [
                            ['url' => $this->getFullUrlPath('/content-test/images/transport/default/58.jpg')],
                            ['url' => $this->getFullUrlPath('/content-test/images/transport/original/58.jpg')],
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
}
