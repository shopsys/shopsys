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
    }

    public function testGetFilteredStoresByPostcode(): void
    {
        $expectedResultName = t('Olomouc', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain());

        $edges = $this->getResponseEdges(searchText: '77900');
        $this->assertCount(1, $edges);
        $this->assertSame($edges[0]['node']['name'], $expectedResultName);
    }

    public function testGetZeroFilteredStores(): void
    {
        $edges = $this->getResponseEdges(searchText: 'non-existent');
        $this->assertCount(0, $edges);
    }

    /**
     * @param string|null $searchText
     * @return array
     */
    private function getResponseEdges(?string $searchText = null): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/StoresFilterQuery.graphql', [
            'searchText' => $searchText,
        ]);

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, 'stores');
        $responseData = $this->getResponseDataForGraphQlType($response, 'stores');

        $this->assertArrayHasKey('edges', $responseData);

        return $responseData['edges'];
    }
}
