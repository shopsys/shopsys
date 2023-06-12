<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Transport;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
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
                "name": "' . t('PPL', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '"
              },
              {
                "name": "' . t('Personal collection', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '"
              },
              {
                "name": "' . t('Drone delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '"
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
                        'name' => t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                        'description' => t('Czech state post service.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                        'instruction' => null,
                        'position' => 0,
                        'daysUntilDelivery' => 5,
                        'transportType' => [
                            'name' => t('Standard', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
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
                            ['name' => t('Cash on delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                            ['name' => t('GoPay - Quick Bank Account Transfer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                        ],
                        'stores' => null,
                    ],
                    [
                        'name' => t('PPL', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                        'description' => null,
                        'instruction' => null,
                        'position' => 1,
                        'daysUntilDelivery' => 4,
                        'transportType' => [
                            'name' => t('Standard', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
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
                            ['name' => t('Credit card', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                            ['name' => t('GoPay - Payment By Card', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                            ['name' => t('GoPay - Quick Bank Account Transfer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                        ],
                        'stores' => null,
                    ],
                    [
                        'name' => t('Personal collection', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                        'description' => t(
                            'You will be welcomed by friendly staff!',
                            [],
                            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                            $this->getLocaleForFirstDomain(),
                        ),
                        'instruction' => null,
                        'position' => 2,
                        'daysUntilDelivery' => 0,
                        'transportType' => [
                            'name' => t('Standard', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
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
                            ['name' => t('Credit card', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                            ['name' => t('Cash', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                            ['name' => t('GoPay - Payment By Card', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                            ['name' => t('GoPay - Quick Bank Account Transfer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                        ],
                        'stores' => [
                            'edges' => [
                                [
                                    'node' => [
                                        'name' => t('Ostrava', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                                    ],
                                ],
                                [
                                    'node' => [
                                        'name' => t('Pardubice', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => t('Drone delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                        'description' => t(
                            'Vhodné pro všechny druhy zboží',
                            [],
                            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                            $this->getLocaleForFirstDomain(),
                        ),
                        'instruction' => null,
                        'position' => 3,
                        'daysUntilDelivery' => 0,
                        'transportType' => [
                            'name' => t('Standard', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                            'code' => 'common',
                        ],
                        'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero),
                        'images' => [],
                        'payments' => [
                            ['name' => t('GoPay - Quick Bank Account Transfer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                            ['name' => t('Pay later', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                        ],
                        'stores' => null,
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }
}
