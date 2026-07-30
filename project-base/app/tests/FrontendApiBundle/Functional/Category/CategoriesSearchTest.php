<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category;

use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class CategoriesSearchTest extends GraphQlTestCase
{
    public function testSearch(): void
    {
        $userIdentifier = Uuid::uuid4()->toString();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CategoriesSearch.graphql', [
            'search' => 'audio',
            'userIdentifier' => $userIdentifier,
        ]);

        $categoriesExpected = [
            ['name' => t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale())],
        ];

        $this->assertCategories($response, $categoriesExpected);
    }

    public function testSearchWithFirstCategory(): void
    {
        $userIdentifier = Uuid::uuid4()->toString();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CategoriesSearch.graphql', [
            'first' => 1,
            'search' => 't',
            'userIdentifier' => $userIdentifier,
        ]);

        $expectedCategoryTranslationKey = $this->getFirstDomainLocale() === 'cs' ? 'TV, audio' : 'Televisions';
        $categoriesExpected = [
            [
                'name' => t(
                    $expectedCategoryTranslationKey,
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $this->getFirstDomainLocale(),
                ),
            ],
        ];

        $this->assertCategories($response, $categoriesExpected);
    }

    public function testSearchWithLastCategory(): void
    {
        $userIdentifier = Uuid::uuid4()->toString();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CategoriesSearch.graphql', [
            'last' => 1,
            'search' => t('audio', [], Translator::TESTS_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            'userIdentifier' => $userIdentifier,
        ]);

        $categoriesExpected = [
            ['name' => t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale())],
        ];

        $this->assertCategories($response, $categoriesExpected);
    }

    private function assertCategories(array $response, array $categories, bool $found = true): void
    {
        $graphQlType = 'categoriesSearch';

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $this->assertArrayHasKey('edges', $responseData);

        $queryResult = [];

        foreach ($responseData['edges'] as $edge) {
            $this->assertArrayHasKey('node', $edge);
            $queryResult[] = $edge['node'];
        }

        if ($found === true) {
            $this->assertEquals($categories, $queryResult, json_encode($queryResult));
        } else {
            $this->assertNotEquals($categories, $queryResult, json_encode($queryResult));
        }
    }
}
