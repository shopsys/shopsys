<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Store;

use Ramsey\Uuid\Uuid;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetStoresTest extends GraphQlTestCase
{
    public function testGetStores(): void
    {
        foreach ($this->getStoresDataProvider() as $dataSet) {
            [$query, $expectedStoresData] = $dataSet;

            $graphQlType = 'stores';
            $response = $this->getResponseContentForQuery($query);
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
                        'address',
                        'openingHours',
                        'specialMessage',
                        'locationLatitude',
                        'locationLongitude',
                    ],
                    $edge['node'],
                    array_shift($expectedStoresData)
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
                $this->getAllStoresQuery(),
                $this->getExpectedStores(),
            ],
            [
                $this->getFirstStoreQuery(),
                array_slice($this->getExpectedStores(), 0, 1),
            ],
            [
                $this->getLastStoreQuery(),
                array_slice($this->getExpectedStores(), 1, 1),
            ],
        ];
    }

    /**
     * @return string
     */
    private function getAllStoresQuery(): string
    {
        $graphQlTypeWithFilters = 'stores';

        return '
            {
                ' . $graphQlTypeWithFilters . ' {
                    edges {
                        node {
                            uuid
                            name
                            isDefault
                            description
                            address
                            openingHours
                            specialMessage
                            locationLatitude
                            locationLongitude
                        }
                    }
                }
            }
        ';
    }

    /**
     * @return string
     */
    private function getFirstStoreQuery(): string
    {
        $graphQlTypeWithFilters = 'stores (first: 1)';

        return '
            {
                ' . $graphQlTypeWithFilters . ' {
                    edges {
                        node {
                            uuid
                            name
                            isDefault
                            description
                            address
                            openingHours
                            specialMessage
                            locationLatitude
                            locationLongitude
                        }
                    }
                }
            }
        ';
    }

    /**
     * @return string
     */
    private function getLastStoreQuery(): string
    {
        $graphQlTypeWithFilters = 'stores (last: 1)';

        return '
            {
                ' . $graphQlTypeWithFilters . ' {
                    edges {
                        node {
                            uuid
                            name
                            isDefault
                            description
                            address
                            openingHours
                            specialMessage
                            locationLatitude
                            locationLongitude
                        }
                    }
                }
            }
        ';
    }

    /**
     * @return array
     */
    private function getExpectedStores(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        return [
            [
                'name' => t('Ostrava', [], 'dataFixtures', $firstDomainLocale),
                'isDefault' => true,
                'description' => null,
                'address' => t('Koksární 10\\n702 00 Ostrava', [], 'dataFixtures', $firstDomainLocale),
                'contactInfo' => null,
                'openingHours' => t('Po-Pa: 8:00-16:00', [], 'dataFixtures', $firstDomainLocale),
                'specialMessage' => null,
                'locationLatitude' => '49.8574975000000',
                'locationLongitude' => '18.2738861000000',
            ], [
                'name' => t('Pardubice', [], 'dataFixtures', $firstDomainLocale),
                'isDefault' => false,
                'description' => null,
                'address' => t('Bratranců Veverkových 2722\\n530 02 Pardubice', [], 'dataFixtures', $firstDomainLocale),
                'contactInfo' => null,
                'openingHours' => t('Po-Pa: 8:00-17:00', [], 'dataFixtures', $firstDomainLocale),
                'specialMessage' => null,
                'locationLatitude' => '50.0346875000000',
                'locationLongitude' => '15.7707169000000',
            ],
        ];
    }
}
