<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category;

use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CategoriesSearchTest extends GraphQlTestCase
{
    public function testSearch(): void
    {
        $userIdentifier = Uuid::uuid4()->toString();

        $query = '
            query {
                categoriesSearch(searchInput: { search: "audio", userIdentifier: "' . $userIdentifier . '" }) {
                    edges {
                        node {
                            name
                        }
                    }
                }
            }';

        $categoriesExpected = [
            ['name' => t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale())],
        ];

        $this->assertCategories($query, $categoriesExpected);
    }

    public function testSearchWithFirstProduct(): void
    {
        $userIdentifier = Uuid::uuid4()->toString();

        $query = '            
            query {
                categoriesSearch(first: 1, searchInput: { search: "a", userIdentifier: "' . $userIdentifier . '" }) {
                    edges {
                        node {
                            name
                        }
                    }
                }
            }';

        $categoriesExpected = [
            ['name' => t('Cameras & Photo', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale())],
        ];

        $this->assertCategories($query, $categoriesExpected);
    }

    public function testSearchWithLastCategory(): void
    {
        $userIdentifier = Uuid::uuid4()->toString();

        $query = '            
            query {
                categoriesSearch(last: 1, searchInput: { search: "' . t('audio', [], Translator::TESTS_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '", userIdentifier: "' . $userIdentifier . '"}) {
                    edges {
                        node {
                            name
                        }
                    }
                }
            }';

        $categoriesExpected = [
            ['name' => t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale())],
        ];

        $this->assertCategories($query, $categoriesExpected);
    }

    /**
     * @param string $query
     * @param array $categories
     * @param bool $found
     */
    protected function assertCategories(string $query, array $categories, bool $found = true): void
    {
        $graphQlType = 'categoriesSearch';

        $response = $this->getResponseContentForQuery($query);

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
