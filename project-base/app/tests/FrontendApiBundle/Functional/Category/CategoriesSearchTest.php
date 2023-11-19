<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CategoriesSearchTest extends GraphQlTestCase
{
    public function testSearch(): void
    {
        $query = '
            query {
                categoriesSearch(search: "audio") {
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
        $query = '            
            query {
                categoriesSearch(first: 1, search: "a") {
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
        $query = '            
            query {
                categoriesSearch(last: 1, search: "' . t('audio', [], Translator::TESTS_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '") {
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
     * @param mixed[] $categories
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
