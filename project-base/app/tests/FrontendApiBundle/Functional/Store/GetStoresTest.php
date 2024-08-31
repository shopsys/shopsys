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
                $this->assertArrayHasKey('node', $edge);

                $this->assertArrayHasKey('uuid', $edge['node']);
                $this->assertTrue(Uuid::isValid($edge['node']['uuid']));

                $this->assertKeysAreSameAsExpected(
                    [
                        'name',
                        'isDefault',
                        'description',
                        'contactInfo',
                        'street',
                        'city',
                        'postcode',
                        'country',
                        'specialMessage',
                        'latitude',
                        'longitude',
                    ],
                    $edge['node'],
                    array_shift($expectedStoresData),
                );
            }
        }
    }

    /**
     * @param array $keys
     * @param array $actual
     * @param array $expected
     */
    private function assertKeysAreSameAsExpected(array $keys, array $actual, array $expected): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $actual);
            $this->assertSame($expected[$key], $actual[$key]);
        }
    }

    /**
     * @return array
     */
    private function getStoresDataProvider(): array
    {
        return [
            [
                $this->getResponseContentForGql(__DIR__ . '/../_graphql/query/StoresQuery.graphql'),
                $this->getExpectedStores(),
            ],
            [
                $this->getResponseContentForGql(__DIR__ . '/../_graphql/query/StoresQuery.graphql', [
                    'first' => 1,
                ]),
                array_slice($this->getExpectedStores(), 0, 1),
            ],
            [
                $this->getResponseContentForGql(__DIR__ . '/../_graphql/query/StoresQuery.graphql', [
                    'last' => 1,
                ]),
                array_slice($this->getExpectedStores(), 7, 1),
            ],
        ];
    }

    /**
     * @return array
     */
    private function getExpectedStores(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();

        return [
            [
                'name' => t('Ostrava', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'isDefault' => true,
                'description' => t('Store in Ostrava Přívoz', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'street' => 'Koksární 10',
                'city' => 'Ostrava',
                'postcode' => '70200',
                'country' => [
                    'code' => 'CZ',
                ],
                'contactInfo' => null,
                'specialMessage' => null,
                'latitude' => '49.8574975',
                'longitude' => '18.2738861',
            ], [
                'name' => t('Pardubice', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'isDefault' => false,
                'description' => t('Store v Pardubice', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'street' => 'Bratranců Veverkových 2722',
                'city' => 'Pardubice',
                'postcode' => '53002',
                'country' => [
                    'code' => 'CZ',
                ],
                'contactInfo' => null,
                'specialMessage' => null,
                'latitude' => '50.0346875',
                'longitude' => '15.7707169',
            ], [
                'name' => t('Brno', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'isDefault' => false,
                'description' => null,
                'street' => 'Křenová 88',
                'city' => 'Brno',
                'postcode' => '60200',
                'country' => [
                    'code' => 'CZ',
                ],
                'contactInfo' => null,
                'specialMessage' => null,
                'latitude' => '49.1950606',
                'longitude' => '16.6084842',
            ], [
                'name' => t('Praha', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'isDefault' => false,
                'description' => null,
                'street' => 'Vodičkova 791/41',
                'city' => 'Praha',
                'postcode' => '11000',
                'country' => [
                    'code' => 'CZ',
                ],
                'contactInfo' => null,
                'specialMessage' => null,
                'latitude' => '50.0802931',
                'longitude' => '14.4208994',
            ], [
                'name' => t('Hradec Králové', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'isDefault' => false,
                'description' => null,
                'street' => 'Pražská 100',
                'city' => 'Hradec Králové',
                'postcode' => '50002',
                'country' => [
                    'code' => 'CZ',
                ],
                'contactInfo' => null,
                'specialMessage' => null,
                'latitude' => '50.2090192',
                'longitude' => '15.8328583',
            ], [
                'name' => t('Olomouc', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'isDefault' => false,
                'description' => null,
                'street' => 'Křížkovského 8',
                'city' => 'Olomouc',
                'postcode' => '77900',
                'country' => [
                    'code' => 'CZ',
                ],
                'contactInfo' => null,
                'specialMessage' => null,
                'latitude' => '49.5951442',
                'longitude' => '17.2500006',
            ], [
                'name' => t('Liberec', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'isDefault' => false,
                'description' => null,
                'street' => 'Šaldova 1',
                'city' => 'Liberec',
                'postcode' => '46001',
                'country' => [
                    'code' => 'CZ',
                ],
                'contactInfo' => null,
                'specialMessage' => null,
                'latitude' => '50.7670131',
                'longitude' => '15.0562825',
            ], [
                'name' => t('Plzeň', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'isDefault' => false,
                'description' => null,
                'street' => 'Klatovská 121',
                'city' => 'Plzeň',
                'postcode' => '30100',
                'country' => [
                    'code' => 'CZ',
                ],
                'contactInfo' => null,
                'specialMessage' => null,
                'latitude' => '49.7476961',
                'longitude' => '13.3777325',
            ],
        ];
    }
}
