<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Transport;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class TransportsTest extends GraphQlTestCase
{
    public function testByCartUuid(): void
    {
        $cartUuid = CartDataFixture::CART_UUID;
        $query = '
            query {
                transports(cartUuid: "' . $cartUuid . '") {
                    name
                }
            }
        ';

        $locale = $this->getFirstDomainLocale();
        $expectedJson = '
        {
          "data": {
            "transports": [
              {
                "name": "' . t('PPL', [], 'dataFixtures', $locale) . '"
              },
              {
                "name": "' . t('Personal collection', [], 'dataFixtures', $locale) . '"
              },
              {
                "name": "' . t('Nadlimitní', [], 'dataFixtures', $locale) . '"
              }
            ]
          }
        }';

        $this->assertQueryWithExpectedJson($query, $expectedJson);
    }

    public function testTransports(): void
    {
        $query = '
            query {
                transports {
                    name,
                    description,
                    instruction,
                    position,
                    daysUntilDelivery
                    transportType {
                        name
                        code
                    }
                    price {
                        priceWithVat
                        priceWithoutVat
                        vatAmount
                    },
                    images {
                        position
                        sizes {
                            url
                        }
                    },
                    payments {
                        name
                    }
                    stores {
                        edges {
                            node {
                                name
                            }
                        }
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
                        'daysUntilDelivery' => 5,
                        'transportType' => [
                            'name' => t('Standardní', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                            'code' => 'common',
                        ],
                        'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh),
                        'images' => [
                            [
                                'position' => null,
                                'sizes' => [
                                    ['url' => $this->getFullUrlPath('/content-test/images/transport/default/56.jpg')],
                                    ['url' => $this->getFullUrlPath('/content-test/images/transport/original/56.jpg')],
                                ],
                            ],
                        ],
                        'payments' => [
                            ['name' => t('Cash on delivery', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ],
                        'stores' => null,
                    ],
                    [
                        'name' => t('PPL', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                        'description' => null,
                        'instruction' => null,
                        'position' => 1,
                        'daysUntilDelivery' => 4,
                        'transportType' => [
                            'name' => t('Standardní', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                            'code' => 'common',
                        ],
                        'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('200', $vatHigh),
                        'images' => [
                            [
                                'position' => null,
                                'sizes' => [
                                    ['url' => $this->getFullUrlPath('/content-test/images/transport/default/57.jpg')],
                                    ['url' => $this->getFullUrlPath('/content-test/images/transport/original/57.jpg')],
                                ],
                            ],
                        ],
                        'payments' => [
                            ['name' => t('Credit card', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                            ['name' => t('GoPay - Platba kartou', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ],
                        'stores' => null,
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
                        'daysUntilDelivery' => 0,
                        'transportType' => [
                            'name' => t('Standardní', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                            'code' => 'common',
                        ],
                        'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero),
                        'images' => [
                            [
                                'position' => null,
                                'sizes' => [
                                    ['url' => $this->getFullUrlPath('/content-test/images/transport/default/58.jpg')],
                                    ['url' => $this->getFullUrlPath('/content-test/images/transport/original/58.jpg')],
                                ],
                            ],
                        ],
                        'payments' => [
                            ['name' => t('Credit card', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                            ['name' => t('Cash', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                            ['name' => t('GoPay - Platba kartou', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ],
                        'stores' => [
                            'edges' => [
                                [
                                    'node' => [
                                        'name' => t('Ostrava', [], 'dataFixtures', $this->getFirstDomainLocale()),
                                    ],
                                ],
                                [
                                    'node' => [
                                        'name' => t('Pardubice', [], 'dataFixtures', $this->getFirstDomainLocale()),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => t('Nadlimitní', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                        'description' => t(
                            'Vhodné pro nadměrné zboží',
                            [],
                            'dataFixtures',
                            $this->getLocaleForFirstDomain()
                        ),
                        'instruction' => null,
                        'position' => 3,
                        'daysUntilDelivery' => 0,
                        'transportType' => [
                            'name' => t('Standardní', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                            'code' => 'common',
                        ],
                        'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero),
                        'images' => [],
                        'payments' => [
                            ['name' => t('Nadlimitní', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ],
                        'stores' => null,
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }
}
