<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Blog\Article;

use App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class BlogArticlesTest extends GraphQlTestCase
{
    private int $totalBlogArticlesCount = 0;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var \App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade $blogArticleFacade */
        $blogArticleFacade = self::getContainer()->get(BlogArticleElasticsearchFacade::class);
        $this->totalBlogArticlesCount = $blogArticleFacade->getAllBlogArticlesTotalCount();
    }

    /**
     * @return array<'case 1'|'case 2'|'case 3'|'case 4'|'case 5', mixed[]>
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
                [['name' => t('Blog article example %counter% %locale%', ['%counter%' => 45, '%locale%' => $firstDomainLocale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)]],
            ],
            'case 5' => [
                $this->getHomepageBlogArticlesQuery(3),
                array_slice($expectedBlogArticlesData, 0, 3),
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
            ['name' => t('Blog article for search testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('Blog article for products testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('GrapesJS page', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('Blog article example %counter% %locale%', ['%counter%' => 1, '%locale%' => $firstDomainLocale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('Blog article example %counter% %locale%', ['%counter%' => 2, '%locale%' => $firstDomainLocale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('Blog article example %counter% %locale%', ['%counter%' => 3, '%locale%' => $firstDomainLocale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('Blog article example %counter% %locale%', ['%counter%' => 4, '%locale%' => $firstDomainLocale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('Blog article example %counter% %locale%', ['%counter%' => 5, '%locale%' => $firstDomainLocale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('Blog article example %counter% %locale%', ['%counter%' => 6, '%locale%' => $firstDomainLocale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('Blog article example %counter% %locale%', ['%counter%' => 7, '%locale%' => $firstDomainLocale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
        ];
    }
}
