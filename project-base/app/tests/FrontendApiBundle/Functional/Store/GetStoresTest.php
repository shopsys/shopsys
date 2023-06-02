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
                        'contactInfo',
                        'street',
                        'city',
                        'postcode',
                        'country',
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
        return $this->getStoresQuery('stores');
    }

    /**
     * @return string
     */
    private function getFirstStoreQuery(): string
    {
        return $this->getStoresQuery('stores (first: 1)');
    }

    /**
     * @return string
     */
    private function getLastStoreQuery(): string
    {
        return $this->getStoresQuery('stores (last: 1)');
    }

    /**
     * @param string $graphQlTypeWithFilters
     * @return string
     */
    private function getStoresQuery(string $graphQlTypeWithFilters): string
    {
        return '
            {
                ' . $graphQlTypeWithFilters . ' {
                    edges {
                        node {
                            uuid
                            name
                            isDefault
                            description
                            contactInfo
                            street
                            city
                            postcode
                            country {
                                code
                            }
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
                'name' => t('Ostrava', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'isDefault' => true,
                'description' => t('Store in Ostrava Přívoz', [], 'dataFixture', $firstDomainLocale),
                'street' => 'Koksární 10',
                'city' => 'Ostrava',
                'postcode' => '70200',
                'country' => [
                    'code' => 'CZ',
                ],
                'contactInfo' => null,
                'openingHours' => t('Po-Pa: 8:00-16:00', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'specialMessage' => null,
                'locationLatitude' => '49.8574975',
                'locationLongitude' => '18.2738861',
            ], [
                'name' => t('Pardubice', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'isDefault' => false,
                'description' => t('Store v Pardubice', [], 'dataFixture', $firstDomainLocale),
                'street' => 'Bratranců Veverkových 2722',
                'city' => 'Pardubice',
                'postcode' => '53002',
                'country' => [
                    'code' => 'CZ',
                ],
                'contactInfo' => null,
                'openingHours' => t('Po-Pa: 8:00-17:00', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'specialMessage' => null,
                'locationLatitude' => '50.0346875',
                'locationLongitude' => '15.7707169',
            ],
        ];
    }
}
