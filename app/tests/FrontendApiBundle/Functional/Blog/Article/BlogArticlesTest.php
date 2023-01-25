<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Blog\Article;

use App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class BlogArticlesTest extends GraphQlTestCase
{
    private int $totalBlogArticlesCount = 0;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var \App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade $blogArticleFacade */
        $blogArticleFacade = $this->getContainer()->get(BlogArticleElasticsearchFacade::class);
        $this->totalBlogArticlesCount = $blogArticleFacade->getAllBlogArticlesTotalCount();
    }

    /**
     * @return array
     */
    private function getBlogArticlesDataProvider(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();

        $expectedBlogArticlesData = $this->getExpectedBlogArticlesData($firstDomainLocale);

        return [
            'case 1' => [
                $this->getAllBlogArticlesQuery(),
                $expectedBlogArticlesData,
            ],
            'case 2' => [
                $this->getFirstBlogArticlesQuery(3),
                array_slice($expectedBlogArticlesData, 0, 3),
            ],
            'case 3' => [
                $this->getFirstBlogArticlesQuery(5),
                array_slice($expectedBlogArticlesData, 0, 5),
            ],
            'case 4' => [
                $this->getLastBlogArticleQuery(),
                [['name' => t('Blog article for search testing', [], 'dataFixtures', $firstDomainLocale)]],
            ],
            'case 5' => [
                $this->getHomepageBlogArticlesQuery(3),
                array_merge(array_slice($expectedBlogArticlesData, 0, 2), array_slice($expectedBlogArticlesData, 3, 1)),
            ],
        ];
    }

    public function testGetBlogArticles(): void
    {
        foreach ($this->getBlogArticlesDataProvider() as $case => $dataSet) {
            [$query, $expectedBlogArticlesData] = $dataSet;

            $graphQlType = 'blogArticles';
            $response = $this->getResponseContentForQuery($query);
            $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
            $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

            $this->assertArrayHasKey('totalCount', $responseData);
            $this->assertSame($this->totalBlogArticlesCount, $responseData['totalCount']);

            $this->assertArrayHasKey('edges', $responseData);
            $this->assertCount(count($expectedBlogArticlesData), $responseData['edges']);
            foreach ($responseData['edges'] as $key => $edge) {
                $this->assertArrayHasKey('node', $edge);
                $this->assertArrayHasKey('name', $edge['node']);
                $this->assertSame($expectedBlogArticlesData[$key]['name'], $edge['node']['name'], $case);
            }
        }
    }

    /**
     * @return string
     */
    private function getAllBlogArticlesQuery(): string
    {
        return '
            {
                blogArticles {
                    edges {
                        node {
                            name
                        }
                    }
                    totalCount
                }
            }
        ';
    }

    /**
     * @param int $limit
     * @return string
     */
    private function getFirstBlogArticlesQuery(int $limit): string
    {
        return '
            {
                blogArticles(first:' . $limit . ') {
                    edges {
                        node {
                            name
                        }
                    }
                    totalCount
                }
            }
        ';
    }

    /**
     * @return string
     */
    private function getLastBlogArticleQuery(): string
    {
        return '
            {
                blogArticles(last:1) {
                    edges {
                        node {
                            name
                        }
                    }
                    totalCount
                }
            }
        ';
    }

    /**
     * @param int $limit
     * @return string
     */
    private function getHomepageBlogArticlesQuery(int $limit): string
    {
        return '
            {
                blogArticles(first:' . $limit . ', onlyHomepageArticles: true) {
                    edges {
                        node {
                            name
                        }
                    }
                    totalCount
                }
            }
        ';
    }

    /**
     * @param string $firstDomainLocale
     * @return array[]
     */
    private function getExpectedBlogArticlesData(string $firstDomainLocale): array
    {
        return [
            ['name' => t('Blog article for products testing', [], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('GrapesJS page', [], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 45, '%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 44, '%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 43, '%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 42, '%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 41, '%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 40, '%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 39, '%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 38, '%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
        ];
    }
}
