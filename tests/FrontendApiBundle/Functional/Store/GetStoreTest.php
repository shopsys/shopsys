<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Store;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetStoreTest extends GraphQlTestCase
{
    /**
     * @var \App\Model\Store\StoreFacade
     * @inject
     */
    private $storeFacade;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade
     * @inject
     */
    private $friendlyUrlFacade;

    public function testGetStoreByUuid(): void
    {
        foreach ($this->getStoreDataProvider() as $dataSet) {
            [$uuid, $expectedStoreData] = $dataSet;

            $graphQlType = 'store';
            $response = $this->getResponseContentForQuery($this->getStoreQueryByUuid($uuid));
            $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
            $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

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
                $responseData,
                $expectedStoreData
            );
        }
    }

    public function testGetStoreByUrlSlug(): void
    {
        foreach ($this->getStoreDataProviderByUrlSlug() as $dataSet) {
            [$urlSlug, $expectedStoreData] = $dataSet;

            $graphQlType = 'store';
            $response = $this->getResponseContentForQuery($this->getStoreQueryByUrlSlug($urlSlug));
            $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
            $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

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
                $responseData,
                $expectedStoreData
            );
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
    private function getStoreDataProvider(): array
    {
        $data = [];
        $storeIds = [1, 2];
        foreach ($storeIds as $storeId) {
            $store = $this->storeFacade->getById($storeId);
            $data[] = [
                $store->getUuid(),
                $this->getExpectedStore($storeId),
            ];
        }
        return $data;
    }

    /**
     * @return array
     */
    private function getStoreDataProviderByUrlSlug(): array
    {
        $data = [];
        $urlSlugs = ['ostrava', 'pardubice'];
        foreach ($urlSlugs as $urlSlug) {
            $friendlyUrl = $this->friendlyUrlFacade->getFriendlyUrlByRouteNameAndSlug(
                Domain::FIRST_DOMAIN_ID,
                'front_stores_detail',
                $urlSlug
            );

            $data[] = [
                $urlSlug,
                $this->getExpectedStore($friendlyUrl->getEntityId()),
            ];
        }
        return $data;
    }

    /**
     * @param string $uuid
     * @return string
     */
    public function getStoreQueryByUuid(string $uuid): string
    {
        $graphQlTypeWithFilters = 'store (uuid:"' . $uuid . '")';
        return $this->getStoreQuery($graphQlTypeWithFilters);
    }

    /**
     * @param string $urlSlug
     * @return string
     */
    public function getStoreQueryByUrlSlug(string $urlSlug): string
    {
        $graphQlTypeWithFilters = 'store (urlSlug:"' . $urlSlug . '")';
        return $this->getStoreQuery($graphQlTypeWithFilters);
    }

    /**
     * @param string $graphQlTypeWithFilters
     * @return string
     */
    private function getStoreQuery(string $graphQlTypeWithFilters): string
    {
        return '
            query {
                ' . $graphQlTypeWithFilters . ' { 
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
        ';
    }

    /**
     * @param int $storeId
     * @return array
     */
    private function getExpectedStore(int $storeId): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $data = [
            1 => [
                'name' => t('Ostrava', [], 'dataFixtures', $firstDomainLocale),
                'isDefault' => true,
                'description' => null,
                'address' => "Koksární 10\n702 00 Ostrava",
                'contactInfo' => null,
                'openingHours' => t('Po-Pa: 8:00-16:00', [], 'dataFixtures', $firstDomainLocale),
                'specialMessage' => null,
                'locationLatitude' => '49.8574975000000',
                'locationLongitude' => '18.2738861000000',
            ],
            2 => [
                'name' => t('Pardubice', [], 'dataFixtures', $firstDomainLocale),
                'isDefault' => false,
                'description' => null,
                'address' => "Bratranců Veverkových 2722\n530 02 Pardubice",
                'contactInfo' => null,
                'openingHours' => t('Po-Pa: 8:00-17:00', [], 'dataFixtures', $firstDomainLocale),
                'specialMessage' => null,
                'locationLatitude' => '50.0346875000000',
                'locationLongitude' => '15.7707169000000',
            ],
        ];
        return $data[$storeId];
    }
}
