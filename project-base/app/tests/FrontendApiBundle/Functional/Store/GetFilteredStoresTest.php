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
        $this->assertCount(1, $edges);
        $this->assertSame($edges[0]['node']['name'], $searchTextName);
        $this->assertNull($edges[0]['node']['distance']);
    }

    public function testGetFilteredStoresByPostcode(): void
    {
        $expectedResultName = t('Olomouc', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain());

        $edges = $this->getResponseEdges(searchText: '77900');
        $this->assertCount(1, $edges);
        $this->assertSame($edges[0]['node']['name'], $expectedResultName);
        $this->assertNull($edges[0]['node']['distance']);
    }

    public function testGetZeroFilteredStores(): void
    {
        $edges = $this->getResponseEdges(searchText: 'non-existent');
        $this->assertCount(0, $edges);
    }

    public function testGetFilteredStoresByCoordinates(): void
    {
        $edges = $this->getResponseEdges(coordinates: ['latitude' => '49.1950602', 'longitude' => '16.6068371']);
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
        $edges = $this->getResponseEdges(searchText: 'B', coordinates: ['latitude' => '50.538331', 'longitude' => '14.485953']);
        $this->assertCount(3, $edges);

        $firstDomainLocale = $this->getLocaleForFirstDomain();

        $expectedResultsData = [
            [
                'name' => t('Liberec', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 47573,
            ],
            [
                'name' => t('Pardubice', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 107087,
            ],
            [
                'name' => t('Brno', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'distance' => 213179,
            ],
        ];

        foreach ($edges as $storeNode) {
            self::assertSame(array_shift($expectedResultsData), $storeNode['node']);
        }
    }

    /**
     * @param string|null $searchText
     * @param array{latitude: string, longitude: string}|null $coordinates
     * @return array
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
