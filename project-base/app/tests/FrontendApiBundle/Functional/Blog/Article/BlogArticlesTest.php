<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Blog\Article;

use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class BlogArticlesTest extends GraphQlTestCase
{
    private int $totalBlogArticlesCount = 0;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        /** @var \Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade $blogArticleFacade */
        $blogArticleFacade = self::getContainer()->get(BlogArticleElasticsearchFacade::class);
        $this->totalBlogArticlesCount = $blogArticleFacade->getAllBlogArticlesTotalCount();
    }

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
                [['name' => $this->translateArticleTitle('OLED, QLED, and Mini LED explained', $firstDomainLocale)]],
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

    public function testHomepageBlogArticlesProvideVariedBadgeCounts(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedCategoryNamesByArticle = [
            [
                t('First subsection %locale%', ['%locale%' => $firstDomainLocale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            ],
            [],
            [],
            [t('Care and maintenance', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            [t('Technology and trends', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            [],
        ];

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/HomepageBlogArticleCategoriesQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, 'blogArticles');
        $actualCategoryNamesByArticle = [];
        $badgeCounts = [];

        foreach ($responseData['edges'] as $edge) {
            $visibleCategories = array_filter(
                $edge['node']['blogCategories'],
                static fn (array $category): bool => $category['parent'] !== null,
            );
            $badgeCounts[] = count($visibleCategories);
            $this->assertLessThanOrEqual(2, count($visibleCategories));
            $actualCategoryNamesByArticle[] = array_values(array_map(
                static fn (array $category): string => $category['name'],
                $visibleCategories,
            ));
        }

        $this->assertContains(0, $badgeCounts);
        $this->assertContains(1, $badgeCounts);
        $this->assertContains(2, $badgeCounts);
        $this->assertSame(
            $expectedCategoryNamesByArticle,
            array_slice($actualCategoryNamesByArticle, 0, count($expectedCategoryNamesByArticle)),
        );
    }

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
     * @return array[]
     */
    private function getExpectedBlogArticlesData(string $firstDomainLocale): array
    {
        return [
            ['name' => t('How to choose the right TV for your living room', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => $this->translateArticleTitle('%topic%: practical advice from our experts', $firstDomainLocale, 'Energy-efficient home')],
            ['name' => $this->translateArticleTitle('%topic%: practical advice from our experts', $firstDomainLocale, 'Online shopping')],
            ['name' => $this->translateArticleTitle('%topic%: practical advice from our experts', $firstDomainLocale, 'Product care')],
            ['name' => $this->translateArticleTitle('%topic%: practical advice from our experts', $firstDomainLocale, 'Smart technology')],
            ['name' => $this->translateArticleTitle('%topic%: how to choose', $firstDomainLocale, 'Television')],
            ['name' => $this->translateArticleTitle('%topic%: how to choose', $firstDomainLocale, 'Headphones')],
            ['name' => $this->translateArticleTitle('%topic%: how to choose', $firstDomainLocale, 'Laptop')],
            ['name' => $this->translateArticleTitle('%topic%: how to choose', $firstDomainLocale, 'Coffee machine')],
            ['name' => $this->translateArticleTitle('%topic%: ideas for a more comfortable home', $firstDomainLocale, 'Home cinema')],
        ];
    }

    private function translateArticleTitle(
        string $translationKey,
        string $locale,
        ?string $topicTranslationKey = null,
    ): string {
        $parameters = [];

        if ($topicTranslationKey !== null) {
            $parameters['%topic%'] = t($topicTranslationKey, [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        return t($translationKey, $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
    }
}
