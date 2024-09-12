<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Transport;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Transport\TransportTypeEnum;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class TransportsTest extends GraphQlTestCase
{
    public function testByCartUuid(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/TransportsQuery.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'transports');

        $locale = $this->getFirstDomainLocale();
        $expectedTransportsData = [
            ['name' => t('PPL', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
            ['name' => t('Personal collection', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
            ['name' => t('Drone delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
            ['name' => t('Packeta', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
        ];
        $this->assertCount(count($expectedTransportsData), $responseData);

        foreach ($expectedTransportsData as $key => $expectedTransport) {
            $this->assertSame($expectedTransport['name'], $responseData[$key]['name']);
        }
    }

    public function testTransports(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/TransportsQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, 'transports');
        $domainId = $this->domain->getId();
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domainId, Vat::class);
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domainId, Vat::class);
        $firstDomainLocale = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();

        $arrayExpected = [
            [
                'name' => t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'description' => t('Czech state post service.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'instruction' => null,
                'position' => 0,
                'daysUntilDelivery' => 5,
                'transportTypeCode' => TransportTypeEnum::TYPE_COMMON,
                'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh),
                'images' => [
                    [
                        'url' => $this->getFullUrlPath('/content-test/images/transport/56.jpg'),
                        'name' => TransportDataFixture::TRANSPORT_CZECH_POST,
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
                'transportTypeCode' => TransportTypeEnum::TYPE_COMMON,
                'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('200', $vatHigh),
                'images' => [
                    [
                        'url' => $this->getFullUrlPath('/content-test/images/transport/57.jpg'),
                        'name' => TransportDataFixture::TRANSPORT_PPL,
                    ],
                ],
                'payments' => [
                    ['name' => t('Credit card', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ['name' => t('Cash on delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
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
                'transportTypeCode' => TransportTypeEnum::TYPE_PERSONAL_PICKUP,
                'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero),
                'images' => [
                    [
                        'url' => $this->getFullUrlPath('/content-test/images/transport/58.jpg'),
                        'name' => TransportDataFixture::TRANSPORT_PERSONAL,
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
                        [
                            'node' => [
                                'name' => t('Brno', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                        [
                            'node' => [
                                'name' => t('Praha', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                        [
                            'node' => [
                                'name' => t('Hradec Králové', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                        [
                            'node' => [
                                'name' => t('Olomouc', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                        [
                            'node' => [
                                'name' => t('Liberec', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                        [
                            'node' => [
                                'name' => t('Plzeň', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => t('Drone delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'description' => t(
                    'Suitable for all kinds of goods',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $this->getLocaleForFirstDomain(),
                ),
                'instruction' => null,
                'position' => 3,
                'daysUntilDelivery' => 0,
                'transportTypeCode' => TransportTypeEnum::TYPE_COMMON,
                'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero),
                'images' => [],
                'payments' => [
                    ['name' => t('GoPay - Quick Bank Account Transfer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ['name' => t('Pay later', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                ],
                'stores' => null,
            ],
            [
                'name' => t('Packeta', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'description' => t(
                    'Packeta delivery company',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $this->getLocaleForFirstDomain(),
                ),
                'instruction' => null,
                'position' => 4,
                'daysUntilDelivery' => 2,
                'transportTypeCode' => TransportTypeEnum::TYPE_PACKETERY,
                'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('49.95', $vatHigh),
                'images' => [],
                'payments' => [
                    ['name' => t('Credit card', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                ],
                'stores' => null,
            ],
        ];

        $this->assertSame($arrayExpected, $responseData);
    }
}
