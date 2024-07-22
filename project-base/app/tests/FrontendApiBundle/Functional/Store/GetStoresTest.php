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
                array_slice($this->getExpectedStores(), 1, 1),
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
            ],
        ];
    }
}
