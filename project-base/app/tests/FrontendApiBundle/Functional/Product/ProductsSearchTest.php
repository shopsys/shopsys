<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

class ProductsSearchTest extends ProductsGraphQlTestCase
{
    public function testSearchInAllProducts(): void
    {
        $firstDomainLocale = $this->getFirstDomainLocale();
        $userIdentifier = Uuid::uuid4()->toString();

        $productsExpected = [
            t('Book scoring system and traffic regulations', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('Book of traditional Czech fairy tales', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('Book Computer for Dummies Digital Photography II', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('Book of procedures for dealing with traffic accidents', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('Book 55 best programs for burning CDs and DVDs', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
        ];

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductsSearch.graphql', [
            'search' => t('book', [], Translator::TESTS_TRANSLATION_DOMAIN, $firstDomainLocale),
            'userIdentifier' => $userIdentifier,
        ]);

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, 'productsSearch');
        $responseData = $this->getResponseDataForGraphQlType($response, 'productsSearch');
        $this->assertArrayHasKey('edges', $responseData);
        $this->assertCount(5, $responseData['edges']);

        foreach ($responseData['edges'] as $edge) {
            $this->assertArrayHasKey('node', $edge);
            $this->assertContains($edge['node']['name'], $productsExpected);
        }
    }

    #[DataProvider('shortSearchTermDataProvider')]
    public function testSearchWorksForShortSearchTerms(string $searchTerm): void
    {
        $firstDomainLocale = $this->getFirstDomainLocale();
        $userIdentifier = Uuid::uuid4()->toString();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductsSearch.graphql', [
            'search' => $searchTerm,
            'userIdentifier' => $userIdentifier,
        ]);

        $expectedFirstProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, 'productsSearch');
        $responseData = $this->getResponseDataForGraphQlType($response, 'productsSearch');
        $this->assertArrayHasKey('edges', $responseData);
        $this->assertSame($expectedFirstProduct->getName($firstDomainLocale), $responseData['edges'][0]['node']['name']);
    }

    /**
     * @return iterable<string, array<string>>
     */
    public static function shortSearchTermDataProvider(): iterable
    {
        yield '1 character search' => [
            'searchTerm' => '2',
        ];

        yield '2 character search' => [
            'searchTerm' => '22',
        ];
    }
}
