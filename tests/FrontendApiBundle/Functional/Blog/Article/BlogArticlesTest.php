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
            [
                $this->getAllBlogArticlesQuery(),
                $expectedBlogArticlesData,
            ], [
                $this->getFirstBlogArticlesQuery(3),
                array_slice($expectedBlogArticlesData, 0, 3),
            ], [
                $this->getFirstBlogArticlesQuery(5),
                array_slice($expectedBlogArticlesData, 0, 5),
            ], [
                $this->getLastBlogArticleQuery(),
                [['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 9, '%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)]],
            ],
        ];
    }

    public function testGetBlogArticles(): void
    {
        foreach ($this->getBlogArticlesDataProvider() as $dataSet) {
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
                $this->assertSame($expectedBlogArticlesData[$key]['name'], $edge['node']['name']);
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
     * @param string $firstDomainLocale
     * @return array[]
     */
    private function getExpectedBlogArticlesData(string $firstDomainLocale): array
    {
        return [
            ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 1, '%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 10, '%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 11, '%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 12, '%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 13, '%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 14, '%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 15, '%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 16, '%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 17, '%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 18, '%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
        ];
    }
}
