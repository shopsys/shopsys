<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Store;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetFilteredStoresTest extends GraphQlTestCase
{
    public function testGetFilteredStoresByCity(): void
    {
        $searchTextName = t('Plzeň', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain());

        $edges = $this->getResponseEdges(searchText: $searchTextName);
        $this->assertCount(8, $edges);

        $firstDomainLocale = $this->getLocaleForFirstDomain();

        $expectedResultsData = [
            [
                'name' => t('Plzeň', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 0,
            ],
            [
                'name' => t('Praha', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 83346,
            ],
            [
                'name' => t('Liberec', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 164571,
            ],
            [
                'name' => t('Pardubice', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 174361,
            ],
            [
                'name' => t('Hradec Králové', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 182889,
            ],
            [
                'name' => t('Brno', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 241376,
            ],
            [
                'name' => t('Olomouc', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 279140,
            ],
            [
                'name' => t('Ostrava', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 351535,
            ],
        ];

        foreach ($edges as $storeNode) {
            self::assertSame(array_shift($expectedResultsData), $storeNode['node']);
        }
    }

    public function testGetFilteredStoresByPostcode(): void
    {
        $edges = $this->getResponseEdges(searchText: '77900');
        $this->assertCount(8, $edges);

        $firstDomainLocale = $this->getLocaleForFirstDomain();

        $expectedResultsData = [
            [
                'name' => t('Olomouc', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 0,
            ],
            [
                'name' => t('Brno', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 64299,
            ],
            [
                'name' => t('Ostrava', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 79167,
            ],
            [
                'name' => t('Pardubice', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 116847,
            ],
            [
                'name' => t('Hradec Králové', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 122311,
            ],
            [
                'name' => t('Liberec', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 203403,
            ],
            [
                'name' => t('Praha', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 209925,
            ],
            [
                'name' => t('Plzeň', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 279140,
            ],
        ];

        foreach ($edges as $storeNode) {
            self::assertSame(array_shift($expectedResultsData), $storeNode['node']);
        }
    }

    public function testGetZeroFilteredStores(): void
    {
        $edges = $this->getResponseEdges(searchText: 'non-existent');
        $this->assertCount(0, $edges);
    }

    public function testGetFilteredStoresByCoordinates(): void
    {
        $edges = $this->getResponseEdges(coordinates: ['latitude' => 49.1950602, 'longitude' => 16.6068371]);
        $this->assertCount(8, $edges);

        $firstDomainLocale = $this->getLocaleForFirstDomain();

        $expectedResultsData = [
            [
                'name' => t('Brno', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 119,
            ],
            [
                'name' => t('Olomouc', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 64386,
            ],
            [
                'name' => t('Pardubice', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 111107,
            ],
            [
                'name' => t('Hradec Králové', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 125736,
            ],
            [
                'name' => t('Ostrava', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 141073,
            ],
            [
                'name' => t('Praha', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 185644,
            ],
            [
                'name' => t('Liberec', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 206978,
            ],
            [
                'name' => t('Plzeň', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 241261,
            ],
        ];

        foreach ($edges as $storeNode) {
            self::assertSame(array_shift($expectedResultsData), $storeNode['node']);
        }
    }

    public function testGetFilteredStoresByCoordinatesAndSearchText(): void
    {
        $edges = $this->getResponseEdges(searchText: 'B', coordinates: ['latitude' => 50.538331, 'longitude' => 14.485953]);
        $this->assertCount(8, $edges);

        $firstDomainLocale = $this->getLocaleForFirstDomain();

        $expectedResultsData = [
            [
                'name' => t('Pardubice', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 0,
            ],
            [
                'name' => t('Hradec Králové', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 19884,
            ],
            [
                'name' => t('Liberec', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 95889,
            ],
            [
                'name' => t('Praha', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 96494,
            ],
            [
                'name' => t('Brno', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 111171,
            ],
            [
                'name' => t('Olomouc', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 116847,
            ],
            [
                'name' => t('Plzeň', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 174361,
            ],
            [
                'name' => t('Ostrava', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 180185,
            ],
        ];

        foreach ($edges as $storeNode) {
            self::assertSame(array_shift($expectedResultsData), $storeNode['node']);
        }
    }

    /**
     * @param array{latitude: float, longitude: float}|null $coordinates
     */
    private function getResponseEdges(?string $searchText = null, ?array $coordinates = null): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/StoresFilterQuery.graphql', [
            'searchText' => $searchText,
            'coordinates' => $coordinates,
        ]);

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, 'stores');
        $responseData = $this->getResponseDataForGraphQlType($response, 'stores');

        $this->assertArrayHasKey('edges', $responseData);

        return $responseData['edges'];
    }
}
