<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Store;

use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetStoresTest extends GraphQlTestCase
{
    public function testGetStores(): void
    {
        foreach ($this->getStoresDataProvider() as $dataSet) {
            [$response, $expectedStoresData] = $dataSet;

            $graphQlType = 'stores';
            $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
            $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

            $this->assertArrayHasKey('edges', $responseData);
            $this->assertCount(count($expectedStoresData), $responseData['edges']);

            foreach ($responseData['edges'] as $edge) {
                $currentExpectedStoreData = array_shift($expectedStoresData);

                $this->assertArrayHasKey('node', $edge);

                $this->assertArrayHasKey('uuid', $edge['node']);
                $this->assertTrue(Uuid::isValid($edge['node']['uuid']));

                $this->assertKeysAreSameAsExpected(
                    [
                        'name',
                        'isDefault',
                        'description',
                        'street',
                        'city',
                        'postcode',
                        'country',
                        'specialMessage',
                    ],
                    $edge['node'],
                    $currentExpectedStoreData,
                );

                $this->assertStringStartsWith($currentExpectedStoreData['latitude'], $edge['node']['latitude']);
                $this->assertStringStartsWith($currentExpectedStoreData['longitude'], $edge['node']['longitude']);
            }
        }
    }

    public function testGetStoresUsesStoreCoordinatesWhenSearchTextMatchesStore(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/StoresWithDistanceQuery.graphql', [
            'first' => 1,
            'searchText' => 'Praha',
        ]);

        $responseData = $this->getResponseDataForGraphQlType($response, 'stores');
        $this->assertSame(
            [
                'searchCoordinates' => [
                    'latitude' => 50.0802931,
                    'longitude' => 14.4208994,
                ],
                'edges' => [
                    [
                        'node' => [
                            'city' => t('Praha', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                            'distance' => 0,
                        ],
                    ],
                ],
            ],
            $responseData,
        );
    }

    private function assertKeysAreSameAsExpected(array $keys, array $actual, array $expected): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $actual);
            $this->assertSame($expected[$key], $actual[$key]);
        }
    }

    private function getStoresDataProvider(): array
    {
        return [
            [
                $this->getResponseContentForGql(__DIR__ . '/graphql/StoresQuery.graphql'),
                $this->getExpectedStores(),
            ],
            [
                $this->getResponseContentForGql(__DIR__ . '/graphql/StoresQuery.graphql', [
                    'first' => 1,
                ]),
                array_slice($this->getExpectedStores(), 0, 1),
            ],
            [
                $this->getResponseContentForGql(__DIR__ . '/graphql/StoresQuery.graphql', [
                    'last' => 1,
                ]),
                array_slice($this->getExpectedStores(), 7, 1),
            ],
        ];
    }

    private function getExpectedStores(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();

        return [
            [
                'name' => t('Ostrava', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'isDefault' => true,
                'description' => t('Pick-up counter is right behind the main entrance. Parking is available in the courtyard for short stops.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'street' => 'Koksární 10',
                'city' => 'Ostrava',
                'postcode' => '70200',
                'country' => [
                    'code' => 'CZ',
                ],
                'specialMessage' => t('Tomorrow will be 20% discount for all items', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'latitude' => '49.8574975',
                'longitude' => '18.2738861',
            ], [
                'name' => t('Pardubice', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'isDefault' => false,
                'description' => t('A compact city store for quick parcel pickup. Please ring the bell if the door is closed during opening hours.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'street' => 'Bratranců Veverkových 2722',
                'city' => 'Pardubice',
                'postcode' => '53002',
                'country' => [
                    'code' => 'CZ',
                ],
                'specialMessage' => null,
                'latitude' => '50.0346875',
                'longitude' => '15.7707169',
            ], [
                'name' => t('Brno', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'isDefault' => false,
                'description' => t('Orders are prepared at the ground-floor counter next to the showroom. Staff can help you check the product before you leave.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'street' => 'Křenová 88',
                'city' => 'Brno',
                'postcode' => '60200',
                'country' => [
                    'code' => 'CZ',
                ],
                'specialMessage' => null,
                'latitude' => '49.1950606',
                'longitude' => '16.6084842',
            ], [
                'name' => t('Praha', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'isDefault' => false,
                'description' => t('The store is located in the city center close to the tram stop. Larger orders can be loaded from the side entrance.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'street' => 'Vodičkova 791/41',
                'city' => 'Praha',
                'postcode' => '11000',
                'country' => [
                    'code' => 'CZ',
                ],
                'specialMessage' => null,
                'latitude' => '50.0802931',
                'longitude' => '14.4208994',
            ], [
                'name' => t('Hradec Králové', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'isDefault' => false,
                'description' => t('This branch is suitable for larger orders. Use the marked customer parking spots next to the warehouse ramp.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'street' => 'Pražská 100',
                'city' => 'Hradec Králové',
                'postcode' => '50002',
                'country' => [
                    'code' => 'CZ',
                ],
                'specialMessage' => null,
                'latitude' => '50.2090192',
                'longitude' => '15.8328583',
            ], [
                'name' => t('Olomouc', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'isDefault' => false,
                'description' => t('Use the pickup window on the left side of the building for prepaid orders. The main entrance stays available for returns and complaints.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'street' => 'Křížkovského 8',
                'city' => 'Olomouc',
                'postcode' => '77900',
                'country' => [
                    'code' => 'CZ',
                ],
                'specialMessage' => null,
                'latitude' => '49.5951442',
                'longitude' => '17.2500006',
            ], [
                'name' => t('Liberec', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'isDefault' => false,
                'description' => t('Pick up your order at the service desk near the entrance. The team can also accept returns on weekdays.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'street' => 'Šaldova 1',
                'city' => 'Liberec',
                'postcode' => '46001',
                'country' => [
                    'code' => 'CZ',
                ],
                'specialMessage' => null,
                'latitude' => '50.7670131',
                'longitude' => '15.0562825',
            ], [
                'name' => t('Plzeň', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'isDefault' => false,
                'description' => t('The pickup desk is inside the shopping passage on the first floor. For bulky orders, please call the store before arrival.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'street' => 'Klatovská 121',
                'city' => 'Plzeň',
                'postcode' => '30100',
                'country' => [
                    'code' => 'CZ',
                ],
                'specialMessage' => null,
                'latitude' => '49.7476961',
                'longitude' => '13.3777325',
            ],
        ];
    }
}
